<?php

namespace App\Console\Commands;

use App\Enums\Finance\InvoiceStatus;
use App\Models\Finance\FinancialInvoice;
use App\Models\User;
use App\Notifications\InvoiceReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendInvoiceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:send-reminders 
                            {--days=7,3,1,0 : Comma-separated days before due date to send reminders}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send invoice reminder notifications to students with upcoming or overdue invoices';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $daysOption = $this->option('days');
        $reminderDays = array_map('intval', explode(',', $daysOption));
        $isDryRun = $this->option('dry-run');

        $this->info('Invoice Reminder Command Started');
        $this->info('Reminder days: ' . implode(', ', $reminderDays));

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No notifications will be sent');
        }

        $sentCount = 0;
        $skippedCount = 0;

        foreach ($reminderDays as $days) {
            $this->line("Processing reminders for {$days} days until due...");
            $count = $this->sendRemindersForDays($days, $isDryRun);
            $sentCount += $count['sent'];
            $skippedCount += $count['skipped'];
        }

        $this->newLine();
        $this->info("Summary: {$sentCount} reminders sent, {$skippedCount} skipped");

        return Command::SUCCESS;
    }

    /**
     * Send reminders for invoices with specific days until due.
     */
    protected function sendRemindersForDays(int $days, bool $isDryRun): array
    {
        $sent = 0;
        $skipped = 0;

        // Calculate target date
        $targetDate = now()->addDays($days)->toDateString();

        // Query overdue or specific due date invoices
        $query = FinancialInvoice::with('riwayatPendidikan.mahasiswa.user')
            ->where('status', InvoiceStatus::UNPAID);

        if ($days <= 0) {
            // Overdue invoices
            $query->where('due_date', '<', now()->toDateString());
        } else {
            // Invoices due on specific date
            $query->whereDate('due_date', $targetDate);
        }

        $invoices = $query->get();

        $this->line("Found {$invoices->count()} invoices for {$days} days reminder");

        foreach ($invoices as $invoice) {
            // Get the student's user account
            $user = $this->getStudentUser($invoice);

            if (!$user) {
                $this->warn("  - Invoice {$invoice->invoice_number}: No user found, skipping");
                $skipped++;
                continue;
            }

            // Check if has pending payment (don't remind if already submitted)
            $hasPendingPayment = $invoice->payments()
                ->where('status', 'PENDING')
                ->exists();

            if ($hasPendingPayment) {
                $this->line("  - Invoice {$invoice->invoice_number}: Has pending payment, skipping");
                $skipped++;
                continue;
            }

            // Check if already sent similar notification today (prevent spam)
            $alreadySentToday = $user->notifications()
                ->whereDate('created_at', now()->toDateString())
                ->where('type', InvoiceReminderNotification::class)
                ->whereJsonContains('data->invoice_id', $invoice->id)
                ->exists();

            if ($alreadySentToday) {
                $this->line("  - Invoice {$invoice->invoice_number}: Already notified today, skipping");
                $skipped++;
                continue;
            }

            // Calculate actual days until due
            $actualDays = (int) now()->diffInDays($invoice->due_date, false);

            if ($isDryRun) {
                $this->info("  - Would notify {$user->name} for invoice {$invoice->invoice_number} ({$actualDays} days)");
            } else {
                $user->notify(new InvoiceReminderNotification($invoice, $actualDays));
                $this->info("  - Notified {$user->name} for invoice {$invoice->invoice_number}");

                Log::info('Invoice reminder sent', [
                    'user_id' => $user->id,
                    'invoice_id' => $invoice->id,
                    'days_until_due' => $actualDays,
                ]);
            }

            $sent++;
        }

        return ['sent' => $sent, 'skipped' => $skipped];
    }

    /**
     * Get the User model for a student from an invoice.
     */
    protected function getStudentUser(FinancialInvoice $invoice): ?User
    {
        $riwayat = $invoice->riwayatPendidikan;
        if (!$riwayat) {
            return null;
        }

        $mahasiswa = $riwayat->mahasiswa;
        if (!$mahasiswa) {
            return null;
        }

        return $mahasiswa->user;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Finance\FinancialInvoice;
use App\Notifications\InvoiceReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendInvoiceDueReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'finance:send-due-reminders 
                            {--days=7,3,1,0 : Comma-separated days before due date to send reminders}';

    /**
     * The console command description.
     */
    protected $description = 'Send reminder notifications to students for invoices nearing due date';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $daysConfig = explode(',', $this->option('days'));
        $daysToCheck = array_map('intval', $daysConfig);

        $this->info('Starting invoice due date reminder check...');
        $this->info('Checking for due dates in: ' . implode(', ', $daysToCheck) . ' days');

        $totalSent = 0;

        foreach ($daysToCheck as $daysUntilDue) {
            $count = $this->sendRemindersForDays($daysUntilDue);
            $totalSent += $count;
        }

        $this->info("Completed. Total notifications sent: {$totalSent}");
        Log::info("[FINANCE] Due date reminders sent: {$totalSent}");

        return Command::SUCCESS;
    }

    /**
     * Send reminders for invoices due in X days
     */
    protected function sendRemindersForDays(int $daysUntilDue): int
    {
        $targetDate = now()->addDays($daysUntilDue)->startOfDay();

        // For overdue (0 days), we check invoices past due
        $query = FinancialInvoice::query()
            ->where('status', 'UNPAID')
            ->whereNotNull('due_date')
            ->with('riwayatPendidikan.mahasiswa.user');

        if ($daysUntilDue === 0) {
            // Overdue invoices
            $query->whereDate('due_date', '<', now()->startOfDay());
        } else {
            // Invoices due exactly in X days
            $query->whereDate('due_date', $targetDate);
        }

        $invoices = $query->get();
        $count = 0;

        foreach ($invoices as $invoice) {
            $user = $invoice->riwayatPendidikan?->mahasiswa?->user;

            if (!$user) {
                $this->warn("No user found for invoice {$invoice->invoice_number}");
                continue;
            }

            // Calculate actual days until due
            $actualDays = $daysUntilDue === 0
                ? (int) now()->diffInDays($invoice->due_date, false)
                : $daysUntilDue;

            // Check if we already sent a notification for this invoice today
            $alreadyNotified = $user->notifications()
                ->where('type', InvoiceReminderNotification::class)
                ->whereDate('created_at', now()->toDateString())
                ->whereJsonContains('data->invoice_id', $invoice->id)
                ->exists();

            if ($alreadyNotified) {
                $this->line("Skipped (already notified today): {$invoice->invoice_number}");
                continue;
            }

            // Send notification
            $user->notify(new InvoiceReminderNotification($invoice, $actualDays));

            $this->line("Sent reminder for {$invoice->invoice_number} to {$user->email} ({$actualDays} days)");
            $count++;
        }

        $label = $daysUntilDue === 0 ? 'overdue' : "{$daysUntilDue} days";
        $this->info("Processed {$label}: {$count} reminders sent");

        return $count;
    }
}

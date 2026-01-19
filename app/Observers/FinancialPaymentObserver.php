<?php

namespace App\Observers;

use App\Enums\Finance\InvoiceStatus;
use App\Enums\Finance\PaymentSource;
use App\Models\Finance\FinancialPayment;
use App\Notifications\PaymentVerifiedNotification;
use Illuminate\Support\Facades\Log;

class FinancialPaymentObserver
{
    /**
     * Handle the FinancialPayment "updated" event.
     * Auto-update linked invoices when payment is verified.
     */
    public function updated(FinancialPayment $payment): void
    {
        // Check if status was just changed to VERIFIED
        if ($payment->isDirty('status') && $payment->status === 'VERIFIED') {
            $this->handlePaymentVerified($payment);
        }

        // Also handle REJECTED status for notification
        if ($payment->isDirty('status') && $payment->status === 'REJECTED') {
            $this->handlePaymentRejected($payment);
        }
    }

    /**
     * Handle verified payment - update all linked invoices to PAID
     */
    protected function handlePaymentVerified(FinancialPayment $payment): void
    {
        // Load invoices if not loaded
        $payment->loadMissing('invoices.riwayatPendidikan.mahasiswa.user');

        foreach ($payment->invoices as $invoice) {
            // Update invoice status to PAID
            $invoice->update([
                'status' => InvoiceStatus::PAID,
                'payment_source' => PaymentSource::SELF_PAYMENT,
                'paid_at' => now(),
            ]);

            Log::info("[FINANCE] Invoice {$invoice->invoice_number} marked as PAID via payment {$payment->payment_number}");

            // Send notification to student
            $this->notifyStudent($invoice, $payment, 'VERIFIED');
        }
    }

    /**
     * Handle rejected payment - notify student
     */
    protected function handlePaymentRejected(FinancialPayment $payment): void
    {
        $payment->loadMissing('invoices.riwayatPendidikan.mahasiswa.user');

        foreach ($payment->invoices as $invoice) {
            $this->notifyStudent($invoice, $payment, 'REJECTED');
        }
    }

    /**
     * Send notification to the student about payment status
     */
    protected function notifyStudent($invoice, FinancialPayment $payment, string $status): void
    {
        // Get the student's user account
        $user = $invoice->riwayatPendidikan?->mahasiswa?->user;

        if ($user) {
            $user->notify(new PaymentVerifiedNotification(
                $payment,
                $status,
                $payment->notes
            ));

            Log::info("[FINANCE] Notified student {$user->id} about payment {$payment->payment_number} status: {$status}");
        }
    }
}

<?php

namespace App\Notifications;

use App\Models\Finance\FinancialPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected FinancialPayment $payment,
        protected string $invoiceNumber,
        protected float $amount
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Pembayaran Baru Menunggu Verifikasi',
            'body' => sprintf(
                'Mahasiswa mengajukan pembayaran %s untuk invoice %s senilai Rp %s.',
                $this->payment->payment_number,
                $this->invoiceNumber,
                number_format($this->amount, 0, ',', '.')
            ),
            'payment_id' => $this->payment->id,
            'payment_number' => $this->payment->payment_number,
            'invoice_number' => $this->invoiceNumber,
            'amount' => $this->amount,
            'payment_method' => $this->payment->payment_method->value,
            'action_url' => '/admin/financial-payments',
            'icon' => 'heroicon-o-banknotes',
            'color' => 'warning',
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}

<?php

namespace App\Notifications;

use App\Models\Finance\FinancialPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentVerifiedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected FinancialPayment $payment,
        protected string $status, // 'VERIFIED' or 'REJECTED'
        protected ?string $notes = null
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
        $isVerified = $this->status === 'VERIFIED';

        $title = $isVerified
            ? '✅ Pembayaran Diverifikasi'
            : '❌ Pembayaran Ditolak';

        $body = $isVerified
            ? sprintf(
                'Pembayaran %s telah diverifikasi. Tagihan Anda telah lunas.',
                $this->payment->payment_number
            )
            : sprintf(
                'Pembayaran %s ditolak.%s',
                $this->payment->payment_number,
                $this->notes ? " Alasan: {$this->notes}" : ' Silakan hubungi bagian keuangan.'
            );

        return [
            'title' => $title,
            'body' => $body,
            'payment_id' => $this->payment->id,
            'payment_number' => $this->payment->payment_number,
            'status' => $this->status,
            'notes' => $this->notes,
            'action_url' => '/mahasiswa/tagihan-saya',
            'icon' => $isVerified ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle',
            'color' => $isVerified ? 'success' : 'danger',
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

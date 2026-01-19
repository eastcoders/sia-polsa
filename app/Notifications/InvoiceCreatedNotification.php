<?php

namespace App\Notifications;

use App\Models\Finance\FinancialInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoiceCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected FinancialInvoice $invoice
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
            'title' => 'Tagihan Baru',
            'body' => sprintf(
                'Tagihan %s periode %s senilai Rp %s telah dibuat.',
                $this->invoice->invoice_number,
                $this->invoice->period_date->format('F Y'),
                number_format($this->invoice->total_amount, 0, ',', '.')
            ),
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => $this->invoice->total_amount,
            'due_date' => $this->invoice->due_date->format('Y-m-d'),
            'action_url' => '/mahasiswa/tagihan-saya',
            'icon' => 'heroicon-o-banknotes',
            'color' => 'info',
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

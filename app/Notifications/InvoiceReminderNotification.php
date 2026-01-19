<?php

namespace App\Notifications;

use App\Models\Finance\FinancialInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoiceReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected FinancialInvoice $invoice,
        protected int $daysUntilDue
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
        $urgency = match (true) {
            $this->daysUntilDue <= 0 => 'OVERDUE',
            $this->daysUntilDue <= 3 => 'URGENT',
            default => 'REMINDER',
        };

        $title = match ($urgency) {
            'OVERDUE' => '⚠️ Tagihan Terlambat!',
            'URGENT' => '⏰ Tagihan Segera Jatuh Tempo',
            'REMINDER' => '📋 Pengingat Tagihan',
        };

        $body = match ($urgency) {
            'OVERDUE' => sprintf(
                'Tagihan %s senilai Rp %s sudah melewati jatuh tempo. Segera lakukan pembayaran.',
                $this->invoice->invoice_number,
                number_format($this->invoice->total_amount, 0, ',', '.')
            ),
            'URGENT' => sprintf(
                'Tagihan %s senilai Rp %s akan jatuh tempo dalam %d hari lagi (%s).',
                $this->invoice->invoice_number,
                number_format($this->invoice->total_amount, 0, ',', '.'),
                $this->daysUntilDue,
                $this->invoice->due_date->format('d M Y')
            ),
            'REMINDER' => sprintf(
                'Tagihan %s senilai Rp %s akan jatuh tempo pada %s (%d hari lagi).',
                $this->invoice->invoice_number,
                number_format($this->invoice->total_amount, 0, ',', '.'),
                $this->invoice->due_date->format('d M Y'),
                $this->daysUntilDue
            ),
        };

        $color = match ($urgency) {
            'OVERDUE' => 'danger',
            'URGENT' => 'warning',
            'REMINDER' => 'info',
        };

        return [
            'title' => $title,
            'body' => $body,
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => $this->invoice->total_amount,
            'due_date' => $this->invoice->due_date->format('Y-m-d'),
            'days_until_due' => $this->daysUntilDue,
            'urgency' => $urgency,
            'action_url' => '/mahasiswa/tagihan-saya',
            'icon' => $urgency === 'OVERDUE' ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-clock',
            'color' => $color,
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

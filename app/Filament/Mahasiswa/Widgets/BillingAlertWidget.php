<?php

namespace App\Filament\Mahasiswa\Widgets;

use App\Enums\Finance\InvoiceStatus;
use App\Models\Finance\FinancialInvoice;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class BillingAlertWidget extends Widget
{
    protected string $view = 'filament.mahasiswa.widgets.billing-alert-widget';

    protected static ?int $sort = -2; // High priority, but after GatekeeperAlertWidget

    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        $user = Auth::user();

        if (!$user || !$user->mahasiswa) {
            return [
                'overdueCount' => 0,
                'overdueAmount' => 0,
                'nearDueCount' => 0,
            ];
        }

        $invoices = FinancialInvoice::whereHas('riwayatPendidikan', function ($query) use ($user) {
            $query->where('id_mahasiswa', $user->mahasiswa->id_mahasiswa);
        })
            ->where('status', InvoiceStatus::UNPAID)
            ->get();

        // Overdue invoices (past due date, no pending payment)
        $overdueInvoices = $invoices->filter(function ($invoice) {
            if (!$invoice->isOverdue()) {
                return false;
            }
            // Exclude if has pending payment
            return !$invoice->payments()->where('status', 'PENDING')->exists();
        });

        // Near due invoices (within 7 days)
        $nearDueInvoices = $invoices->filter(function ($invoice) {
            if ($invoice->isOverdue()) {
                return false;
            }
            $daysLeft = now()->diffInDays($invoice->due_date, false);
            return $daysLeft <= 7 && $daysLeft >= 0;
        });

        return [
            'overdueCount' => $overdueInvoices->count(),
            'overdueAmount' => $overdueInvoices->sum('total_amount'),
            'nearDueCount' => $nearDueInvoices->count(),
            'nearDueAmount' => $nearDueInvoices->sum('total_amount'),
        ];
    }

    public static function canView(): bool
    {
        $user = Auth::user();

        if (!$user || !$user->mahasiswa) {
            return false;
        }

        // Show widget if there are overdue or near-due invoices
        $hasOverdue = FinancialInvoice::whereHas('riwayatPendidikan', function ($query) use ($user) {
            $query->where('id_mahasiswa', $user->mahasiswa->id_mahasiswa);
        })
            ->where('status', InvoiceStatus::UNPAID)
            ->where(function ($query) {
                // Overdue
                $query->where('due_date', '<', now())
                    // OR near due (within 7 days)
                    ->orWhereBetween('due_date', [now(), now()->addDays(7)]);
            })
            ->exists();

        return $hasOverdue;
    }
}

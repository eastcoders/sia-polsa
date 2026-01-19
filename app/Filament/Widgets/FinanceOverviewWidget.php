<?php

namespace App\Filament\Widgets;

use App\Models\Finance\FinancialInvoice;
use App\Models\Finance\FinancialPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinanceOverviewWidget extends BaseWidget
{
    protected ?string $pollingInterval = '30s';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $currentMonth = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();

        // Total invoices this month
        $totalInvoicesThisMonth = FinancialInvoice::query()
            ->whereBetween('period_date', [$currentMonth, $currentMonthEnd])
            ->count();

        $totalAmountThisMonth = FinancialInvoice::query()
            ->whereBetween('period_date', [$currentMonth, $currentMonthEnd])
            ->sum('total_amount');

        // Paid invoices
        $paidAmount = FinancialInvoice::query()
            ->where('status', 'PAID')
            ->whereBetween('period_date', [$currentMonth, $currentMonthEnd])
            ->sum('total_amount');

        $paidCount = FinancialInvoice::query()
            ->where('status', 'PAID')
            ->whereBetween('period_date', [$currentMonth, $currentMonthEnd])
            ->count();

        // Outstanding (UNPAID)
        $outstandingAmount = FinancialInvoice::query()
            ->where('status', 'UNPAID')
            ->sum('total_amount');

        $outstandingCount = FinancialInvoice::query()
            ->where('status', 'UNPAID')
            ->count();

        // Overdue
        $overdueCount = FinancialInvoice::query()
            ->where('status', 'UNPAID')
            ->where('due_date', '<', now())
            ->count();

        // Pending verification
        $pendingPayments = FinancialPayment::query()
            ->where('status', 'PENDING')
            ->count();

        return [
            Stat::make('Tagihan Bulan Ini', $this->formatCurrency($totalAmountThisMonth))
                ->description("{$totalInvoicesThisMonth} tagihan")
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Terbayar Bulan Ini', $this->formatCurrency($paidAmount))
                ->description("{$paidCount} tagihan lunas")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total Outstanding', $this->formatCurrency($outstandingAmount))
                ->description("{$outstandingCount} belum lunas" . ($overdueCount > 0 ? " ({$overdueCount} terlambat)" : ""))
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($overdueCount > 0 ? 'danger' : 'warning'),

            Stat::make('Menunggu Verifikasi', $pendingPayments)
                ->description('Pembayaran pending')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingPayments > 0 ? 'warning' : 'gray')
                ->url(route('filament.admin.resources.finance.financial-payments.index')),
        ];
    }

    /**
     * Format amount to Indonesian Rupiah
     */
    protected function formatCurrency(float $amount): string
    {
        if ($amount >= 1_000_000_000) {
            return 'Rp ' . number_format($amount / 1_000_000_000, 1, ',', '.') . ' M';
        } elseif ($amount >= 1_000_000) {
            return 'Rp ' . number_format($amount / 1_000_000, 1, ',', '.') . ' Jt';
        } else {
            return 'Rp ' . number_format($amount, 0, ',', '.');
        }
    }
}

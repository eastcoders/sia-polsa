<?php

namespace App\Filament\Mahasiswa\Widgets;

use App\Enums\Finance\InvoiceStatus;
use App\Models\Finance\FinancialInvoice;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class TagihanSummaryWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Auth::user();

        if (!$user || !$user->mahasiswa) {
            return [];
        }

        $invoices = FinancialInvoice::whereHas('riwayatPendidikan', function ($query) use ($user) {
            $query->where('id_mahasiswa', $user->mahasiswa->id_mahasiswa);
        })->with('payments')->get();

        $unpaidInvoices = $invoices->where('status', InvoiceStatus::UNPAID);
        $paidInvoices = $invoices->where('status', InvoiceStatus::PAID);

        // Calculate pending verification amount
        $pendingAmount = 0;
        $pendingCount = 0;
        $pureUnpaidAmount = 0;

        foreach ($unpaidInvoices as $invoice) {
            $hasPending = $invoice->payments->where('status', 'PENDING')->count() > 0;
            if ($hasPending) {
                $pendingAmount += $invoice->total_amount;
                $pendingCount++;
            } else {
                $pureUnpaidAmount += $invoice->total_amount;
            }
        }

        return [
            Stat::make('Belum Dibayar', 'Rp ' . number_format($pureUnpaidAmount, 0, ',', '.'))
                ->description($unpaidInvoices->count() - $pendingCount . ' tagihan')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),

            Stat::make('Menunggu Verifikasi', 'Rp ' . number_format($pendingAmount, 0, ',', '.'))
                ->description($pendingCount . ' pembayaran')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Lunas', 'Rp ' . number_format($paidInvoices->sum('total_amount'), 0, ',', '.'))
                ->description($paidInvoices->count() . ' tagihan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}

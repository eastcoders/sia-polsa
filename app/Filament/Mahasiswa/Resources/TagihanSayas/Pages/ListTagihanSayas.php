<?php

namespace App\Filament\Mahasiswa\Resources\TagihanSayas\Pages;

use App\Enums\Finance\InvoiceStatus;
use App\Filament\Mahasiswa\Resources\TagihanSayas\TagihanSayaResource;
use App\Models\Finance\FinancialInvoice;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListTagihanSayas extends ListRecords
{
    protected static string $resource = TagihanSayaResource::class;

    protected static ?string $title = 'Tagihan Saya';

    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Mahasiswa\Widgets\TagihanSummaryWidget::class,
        ];
    }

    /**
     * Get invoice summary stats for the header
     */
    protected function getInvoiceStats(): array
    {
        $user = Auth::user();

        if (!$user || !$user->mahasiswa) {
            return [
                'total_unpaid' => 0,
                'total_pending' => 0,
                'total_paid' => 0,
                'unpaid_count' => 0,
            ];
        }

        $invoices = FinancialInvoice::whereHas('riwayatPendidikan', function ($query) use ($user) {
            $query->where('id_mahasiswa', $user->mahasiswa->id_mahasiswa);
        })->get();

        $unpaidInvoices = $invoices->where('status', InvoiceStatus::UNPAID);
        $paidInvoices = $invoices->where('status', InvoiceStatus::PAID);

        // Calculate pending verification amount
        $pendingAmount = 0;
        foreach ($unpaidInvoices as $invoice) {
            $hasPending = $invoice->payments()->where('status', 'PENDING')->exists();
            if ($hasPending) {
                $pendingAmount += $invoice->total_amount;
            }
        }

        return [
            'total_unpaid' => $unpaidInvoices->sum('total_amount') - $pendingAmount,
            'total_pending' => $pendingAmount,
            'total_paid' => $paidInvoices->sum('total_amount'),
            'unpaid_count' => $unpaidInvoices->count(),
        ];
    }
}

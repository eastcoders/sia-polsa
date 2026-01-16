<?php

namespace App\Filament\Dosen\Resources\Finance\FinancialInvoices\Pages;

use App\Filament\Dosen\Resources\Finance\FinancialInvoices\FinancialInvoiceResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListFinancialInvoices extends ListRecords
{
    protected static string $resource = FinancialInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

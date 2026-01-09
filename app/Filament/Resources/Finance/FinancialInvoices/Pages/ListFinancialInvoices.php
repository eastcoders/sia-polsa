<?php

namespace App\Filament\Resources\Finance\FinancialInvoices\Pages;

use App\Filament\Resources\Finance\FinancialInvoices\FinancialInvoiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFinancialInvoices extends ListRecords
{
    protected static string $resource = FinancialInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

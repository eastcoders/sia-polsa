<?php

namespace App\Filament\Resources\Finance\FinancialInvoices\Pages;

use App\Filament\Resources\Finance\FinancialInvoices\FinancialInvoiceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFinancialInvoice extends EditRecord
{
    protected static string $resource = FinancialInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

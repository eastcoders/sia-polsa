<?php

namespace App\Filament\Dosen\Resources\Finance\FinancialInvoices\Pages;

use App\Filament\Dosen\Resources\Finance\FinancialInvoices\FinancialInvoiceResource;
use Filament\Resources\Pages\EditRecord;

class EditFinancialInvoice extends EditRecord
{
    protected static string $resource = FinancialInvoiceResource::class;
}

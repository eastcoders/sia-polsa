<?php

namespace App\Filament\Dosen\Resources\Finance\FinancialInvoices\Pages;

use App\Filament\Dosen\Resources\Finance\FinancialInvoices\FinancialInvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFinancialInvoice extends CreateRecord
{
    protected static string $resource = FinancialInvoiceResource::class;
}

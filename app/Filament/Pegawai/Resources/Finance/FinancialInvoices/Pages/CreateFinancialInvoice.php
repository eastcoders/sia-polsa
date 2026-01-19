<?php

namespace App\Filament\Pegawai\Resources\Finance\FinancialInvoices\Pages;

use App\Filament\Pegawai\Resources\Finance\FinancialInvoices\FinancialInvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFinancialInvoice extends CreateRecord
{
    protected static string $resource = FinancialInvoiceResource::class;
}

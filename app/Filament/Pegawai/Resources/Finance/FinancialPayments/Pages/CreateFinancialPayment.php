<?php

namespace App\Filament\Pegawai\Resources\Finance\FinancialPayments\Pages;

use App\Filament\Pegawai\Resources\Finance\FinancialPayments\FinancialPaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFinancialPayment extends CreateRecord
{
    protected static string $resource = FinancialPaymentResource::class;
}

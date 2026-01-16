<?php

namespace App\Filament\Dosen\Resources\Finance\FinancialPayments\Pages;

use App\Filament\Dosen\Resources\Finance\FinancialPayments\FinancialPaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFinancialPayment extends CreateRecord
{
    protected static string $resource = FinancialPaymentResource::class;
}

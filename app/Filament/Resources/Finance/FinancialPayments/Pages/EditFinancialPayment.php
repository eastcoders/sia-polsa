<?php

namespace App\Filament\Resources\Finance\FinancialPayments\Pages;

use App\Filament\Resources\Finance\FinancialPayments\FinancialPaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFinancialPayment extends EditRecord
{
    protected static string $resource = FinancialPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

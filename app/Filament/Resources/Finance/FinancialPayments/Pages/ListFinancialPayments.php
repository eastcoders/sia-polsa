<?php

namespace App\Filament\Resources\Finance\FinancialPayments\Pages;

use App\Filament\Resources\Finance\FinancialPayments\FinancialPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFinancialPayments extends ListRecords
{
    protected static string $resource = FinancialPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

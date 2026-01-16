<?php

namespace App\Filament\Dosen\Resources\Finance\FinancialPayments\Pages;

use App\Filament\Dosen\Resources\Finance\FinancialPayments\FinancialPaymentResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListFinancialPayments extends ListRecords
{
    protected static string $resource = FinancialPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

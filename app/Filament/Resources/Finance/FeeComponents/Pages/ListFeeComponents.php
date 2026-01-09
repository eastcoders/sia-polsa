<?php

namespace App\Filament\Resources\Finance\FeeComponents\Pages;

use App\Filament\Resources\Finance\FeeComponents\FeeComponentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeeComponents extends ListRecords
{
    protected static string $resource = FeeComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

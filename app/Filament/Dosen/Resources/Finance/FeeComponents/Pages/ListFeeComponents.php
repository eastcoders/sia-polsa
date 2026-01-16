<?php

namespace App\Filament\Dosen\Resources\Finance\FeeComponents\Pages;

use App\Filament\Dosen\Resources\Finance\FeeComponents\FeeComponentResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListFeeComponents extends ListRecords
{
    protected static string $resource = FeeComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

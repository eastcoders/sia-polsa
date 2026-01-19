<?php

namespace App\Filament\Pegawai\Resources\Finance\FeeStructures\Pages;

use App\Filament\Pegawai\Resources\Finance\FeeStructures\FeeStructureResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListFeeStructures extends ListRecords
{
    protected static string $resource = FeeStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

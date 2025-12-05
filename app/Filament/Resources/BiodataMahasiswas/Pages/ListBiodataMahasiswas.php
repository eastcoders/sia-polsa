<?php

namespace App\Filament\Resources\BiodataMahasiswas\Pages;

use App\Filament\Resources\BiodataMahasiswas\BiodataMahasiswaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBiodataMahasiswas extends ListRecords
{
    protected static string $resource = BiodataMahasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

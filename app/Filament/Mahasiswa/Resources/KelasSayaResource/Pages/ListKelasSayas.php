<?php

namespace App\Filament\Mahasiswa\Resources\KelasSayaResource\Pages;

use App\Filament\Mahasiswa\Resources\KelasSayaResource;
use Filament\Resources\Pages\ListRecords;

class ListKelasSayas extends ListRecords
{
    protected static string $resource = KelasSayaResource::class;

    protected function getHeaderActions(): array
    {
        // No actions needed for read-only view
        return [];
    }
}

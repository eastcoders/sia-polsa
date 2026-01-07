<?php

namespace App\Filament\Mahasiswa\Resources\PengajuanSurats\Pages;

use App\Filament\Mahasiswa\Resources\PengajuanSurats\PengajuanSuratResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanSurats extends ListRecords
{
    protected static string $resource = PengajuanSuratResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

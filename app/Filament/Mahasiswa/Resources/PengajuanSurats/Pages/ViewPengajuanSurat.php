<?php

namespace App\Filament\Mahasiswa\Resources\PengajuanSurats\Pages;

use App\Filament\Mahasiswa\Resources\PengajuanSurats\PengajuanSuratResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPengajuanSurat extends ViewRecord
{
    protected static string $resource = PengajuanSuratResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

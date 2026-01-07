<?php

namespace App\Filament\Mahasiswa\Resources\PengajuanSurats\Pages;

use App\Filament\Mahasiswa\Resources\PengajuanSurats\PengajuanSuratResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPengajuanSurat extends EditRecord
{
    protected static string $resource = PengajuanSuratResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}

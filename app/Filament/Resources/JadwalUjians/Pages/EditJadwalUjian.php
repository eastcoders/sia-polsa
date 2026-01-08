<?php

namespace App\Filament\Resources\JadwalUjians\Pages;

use App\Filament\Resources\JadwalUjians\JadwalUjianResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditJadwalUjian extends EditRecord
{
    protected static string $resource = JadwalUjianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

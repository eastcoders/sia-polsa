<?php

namespace App\Filament\Resources\BiodataMahasiswas\Pages;

use App\Filament\Resources\BiodataMahasiswas\BiodataMahasiswaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMahasiswaDetail extends EditRecord
{
    protected static string $resource = BiodataMahasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

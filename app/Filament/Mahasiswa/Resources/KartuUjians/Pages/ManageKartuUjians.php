<?php

namespace App\Filament\Mahasiswa\Resources\KartuUjians\Pages;

use App\Filament\Mahasiswa\Resources\KartuUjians\KartuUjianResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageKartuUjians extends ManageRecords
{
    protected static string $resource = KartuUjianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

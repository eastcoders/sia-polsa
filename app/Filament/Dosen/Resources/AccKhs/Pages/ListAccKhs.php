<?php

namespace App\Filament\Dosen\Resources\AccKhs\Pages;

use App\Filament\Dosen\Resources\AccKhs\AccKhsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccKhs extends ListRecords
{
    protected static string $resource = AccKhsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

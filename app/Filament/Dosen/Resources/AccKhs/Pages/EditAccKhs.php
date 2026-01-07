<?php

namespace App\Filament\Dosen\Resources\AccKhs\Pages;

use App\Filament\Dosen\Resources\AccKhs\AccKhsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditAccKhs extends EditRecord
{
    protected static string $resource = AccKhsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}

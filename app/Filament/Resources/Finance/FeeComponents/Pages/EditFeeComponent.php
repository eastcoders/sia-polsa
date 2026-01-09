<?php

namespace App\Filament\Resources\Finance\FeeComponents\Pages;

use App\Filament\Resources\Finance\FeeComponents\FeeComponentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeeComponent extends EditRecord
{
    protected static string $resource = FeeComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

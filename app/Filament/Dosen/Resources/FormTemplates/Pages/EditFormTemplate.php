<?php

namespace App\Filament\Dosen\Resources\FormTemplates\Pages;

use App\Filament\Dosen\Resources\FormTemplates\FormTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFormTemplate extends EditRecord
{
    protected static string $resource = FormTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Dosen\Resources\FormTemplates\Pages;

use App\Filament\Dosen\Resources\FormTemplates\FormTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFormTemplates extends ListRecords
{
    protected static string $resource = FormTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

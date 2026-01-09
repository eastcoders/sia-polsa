<?php

namespace App\Filament\Resources\Finance\ExamDispensations\Pages;

use App\Filament\Resources\Finance\ExamDispensations\ExamDispensationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExamDispensations extends ListRecords
{
    protected static string $resource = ExamDispensationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Dosen\Resources\Finance\ExamDispensations\Pages;

use App\Filament\Dosen\Resources\Finance\ExamDispensations\ExamDispensationResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListExamDispensations extends ListRecords
{
    protected static string $resource = ExamDispensationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Pegawai\Resources\Finance\ExamDispensations\Pages;

use App\Filament\Pegawai\Resources\Finance\ExamDispensations\ExamDispensationResource;
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

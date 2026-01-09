<?php

namespace App\Filament\Resources\Finance\ExamDispensations\Pages;

use App\Filament\Resources\Finance\ExamDispensations\ExamDispensationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExamDispensation extends EditRecord
{
    protected static string $resource = ExamDispensationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

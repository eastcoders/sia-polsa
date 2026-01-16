<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finance\StudentScholarships\Pages;

use App\Filament\Resources\Finance\StudentScholarships\StudentScholarshipResource;
use Filament\Resources\Pages\EditRecord;

class EditStudentScholarship extends EditRecord
{
    protected static string $resource = StudentScholarshipResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

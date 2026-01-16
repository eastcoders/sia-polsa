<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finance\StudentScholarships\Pages;

use App\Filament\Resources\Finance\StudentScholarships\StudentScholarshipResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStudentScholarship extends CreateRecord
{
    protected static string $resource = StudentScholarshipResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set approved_by and approved_at if status is ACTIVE
        if (($data['status'] ?? null) === 'ACTIVE') {
            $data['approved_by'] = auth()->id();
            $data['approved_at'] = now();
        }

        return $data;
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finance\Scholarships\Pages;

use App\Filament\Resources\Finance\Scholarships\ScholarshipResource;
use Filament\Resources\Pages\CreateRecord;

class CreateScholarship extends CreateRecord
{
    protected static string $resource = ScholarshipResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

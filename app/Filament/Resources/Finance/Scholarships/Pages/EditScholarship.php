<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finance\Scholarships\Pages;

use App\Filament\Resources\Finance\Scholarships\ScholarshipResource;
use Filament\Resources\Pages\EditRecord;

class EditScholarship extends EditRecord
{
    protected static string $resource = ScholarshipResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

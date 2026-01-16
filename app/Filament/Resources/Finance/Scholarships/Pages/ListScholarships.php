<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finance\Scholarships\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Finance\Scholarships\ScholarshipResource;

class ListScholarships extends ListRecords
{
    protected static string $resource = ScholarshipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

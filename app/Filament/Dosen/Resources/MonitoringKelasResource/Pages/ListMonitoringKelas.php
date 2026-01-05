<?php

namespace App\Filament\Dosen\Resources\MonitoringKelasResource\Pages;

use App\Filament\Dosen\Resources\MonitoringKelasResource;
use Filament\Resources\Pages\ListRecords;

class ListMonitoringKelas extends ListRecords
{
    protected static string $resource = MonitoringKelasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No Create Action needed for Monitoring
        ];
    }
}

<?php

namespace App\Filament\Dosen\Resources\MonitoringMahasiswaResource\Pages;

use App\Filament\Dosen\Resources\MonitoringMahasiswaResource;
use Filament\Resources\Pages\ListRecords;

class ListMonitoringMahasiswa extends ListRecords
{
    protected static string $resource = MonitoringMahasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}

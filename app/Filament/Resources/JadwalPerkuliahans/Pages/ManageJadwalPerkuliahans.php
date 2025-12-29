<?php

namespace App\Filament\Resources\JadwalPerkuliahans\Pages;

use App\Filament\Resources\JadwalPerkuliahans\JadwalPerkuliahanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class ManageJadwalPerkuliahans extends ManageRecords
{
    protected static string $resource = JadwalPerkuliahanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Data')
                ->icon(Heroicon::Plus)
                ->slideOver()
                ->modalWidth(Width::Medium),
        ];
    }
}

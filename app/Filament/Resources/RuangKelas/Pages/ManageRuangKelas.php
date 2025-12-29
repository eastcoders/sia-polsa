<?php

namespace App\Filament\Resources\RuangKelas\Pages;

use App\Filament\Resources\RuangKelas\RuangKelasResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class ManageRuangKelas extends ManageRecords
{
    protected static string $resource = RuangKelasResource::class;

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

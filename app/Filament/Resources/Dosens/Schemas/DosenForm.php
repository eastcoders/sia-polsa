<?php

namespace App\Filament\Resources\Dosens\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DosenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id_dosen')
                    ->required(),
                TextInput::make('nama_dosen')
                    ->required(),
                TextInput::make('nidn'),
                TextInput::make('nip'),
                TextInput::make('jenis_kelamin')
                    ->required(),
                TextInput::make('id_agama')
                    ->required(),
                TextInput::make('tanggal_lahir')
                    ->required(),
                TextInput::make('id_status_aktif')
                    ->required(),
                DateTimePicker::make('sync_at'),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Finance\FeeStructures\Schemas;

use App\Models\Semester;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class FeeStructureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('angkatan')
                    ->options(fn() => Semester::orderBy('id_tahun_ajaran', 'desc')->pluck('id_tahun_ajaran', 'id_tahun_ajaran'))
                    ->searchable()
                    ->required(),
                Select::make('prodi_id')
                    ->label('Program Studi')
                    ->relationship('prodi', 'nama_program_studi')
                    ->required(),
                Select::make('waktu_kuliah_enum')
                    ->options(['pagi' => 'Pagi', 'sore' => 'Sore'])
                    ->required(),
                Select::make('fee_component_id')
                    ->relationship('component', 'name')
                    ->required(),
                TextInput::make('amount')
                    ->label('Jumlah')
                    ->prefix('Rp.')
                    ->required()
                    ->numeric(),
            ]);
    }
}

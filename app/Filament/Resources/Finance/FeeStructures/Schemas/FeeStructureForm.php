<?php

namespace App\Filament\Resources\Finance\FeeStructures\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FeeStructureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('angkatan')
                    ->required(),
                TextInput::make('prodi_id'),
                Select::make('waktu_kuliah_enum')
                    ->options(['pagi' => 'Pagi', 'sore' => 'Sore']),
                TextInput::make('fee_component_id')
                    ->required()
                    ->numeric(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
            ]);
    }
}

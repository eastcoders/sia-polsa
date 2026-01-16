<?php

namespace App\Filament\Resources\Finance\FeeComponents\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FeeComponentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Komponen')
                    ->required(),
                Select::make('type')
                    ->label('Jenis Biaya')
                    ->options([
                        'RECURRING' => 'Berulang (Bulanan/Semester)',
                        'ONE_TIME' => 'Sekali Bayar',
                    ])
                    ->default('RECURRING')
                    ->required(),
            ]);
    }
}

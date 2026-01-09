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
                    ->required(),
                Select::make('type')
                    ->options(['RECURRING' => 'R e c u r r i n g', 'ONE_TIME' => 'O n e  t i m e'])
                    ->default('RECURRING')
                    ->required(),
            ]);
    }
}

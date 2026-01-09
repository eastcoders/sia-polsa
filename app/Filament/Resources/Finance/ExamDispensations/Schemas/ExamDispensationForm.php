<?php

namespace App\Filament\Resources\Finance\ExamDispensations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ExamDispensationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id_registrasi_mahasiswa')
                    ->required(),
                Select::make('type')
                    ->options(['UTS' => 'U t s', 'UAS' => 'U a s', 'KRS' => 'K r s'])
                    ->required(),
                DatePicker::make('valid_until')
                    ->required(),
                Textarea::make('reason')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('approved_by')
                    ->required()
                    ->numeric(),
            ]);
    }
}

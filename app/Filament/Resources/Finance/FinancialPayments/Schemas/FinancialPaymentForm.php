<?php

namespace App\Filament\Resources\Finance\FinancialPayments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FinancialPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('payment_number')
                    ->required(),
                Select::make('payment_method')
                    ->options([
            'MANUAL_TRANSFER' => 'M a n u a l  t r a n s f e r',
            'VIRTUAL_ACCOUNT' => 'V i r t u a l  a c c o u n t',
            'CASH' => 'C a s h',
        ])
                    ->required(),
                TextInput::make('proof_file_path'),
                TextInput::make('proof_file_hash'),
                Select::make('status')
                    ->options(['PENDING' => 'P e n d i n g', 'VERIFIED' => 'V e r i f i e d', 'REJECTED' => 'R e j e c t e d'])
                    ->default('PENDING')
                    ->required(),
                DateTimePicker::make('verified_at'),
                TextInput::make('verified_by')
                    ->numeric(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}

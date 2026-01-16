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
                        'MANUAL_TRANSFER' => 'Manual transfer',
                        'VIRTUAL_ACCOUNT' => 'Virtual  account',
                        'CASH' => 'Cash',
                    ])
                    ->required(),
                TextInput::make('proof_file_path'),
                TextInput::make('proof_file_hash'),
                Select::make('status')
                    ->options(['PENDING' => 'Pending', 'VERIFIED' => 'Verified', 'REJECTED' => 'Rejected'])
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

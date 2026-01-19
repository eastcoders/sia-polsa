<?php

namespace App\Filament\Resources\Finance\FinancialPayments\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DateTimePicker;

class FinancialPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pembayaran')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('payment_number')
                                    ->label('Nomor Pembayaran')
                                    ->required()
                                    ->readOnly(),
                                Select::make('payment_method')
                                    ->label('Metode Pembayaran')
                                    ->options(\App\Enums\Finance\PaymentMethod::class)
                                    ->required(),
                            ]),
                    ]),

                Section::make('Bukti Pembayaran')
                    ->icon('heroicon-o-document-arrow-up')
                    ->schema([
                        FileUpload::make('proof_file_path')
                            ->label('Upload Bukti')
                            ->helperText('Format yang diterima: JPG, PNG, atau PDF')
                            ->storeFileNamesIn('original_filename')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                            ->openable()
                            ->downloadable()
                            ->previewable(true)
                            ->visibility('private')
                            ->columnSpanFull(),
                        TextInput::make('proof_file_hash')
                            ->label('Hash File')
                            ->disabled()
                            ->dehydrated(false)
                            ->hidden(),
                    ]),

                Section::make('Status & Verifikasi')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('status')
                                    ->label('Status Pembayaran')
                                    ->options([
                                        'PENDING' => 'Menunggu Verifikasi',
                                        'VERIFIED' => 'Terverifikasi',
                                        'REJECTED' => 'Ditolak',
                                    ])
                                    ->default('PENDING')
                                    ->required(),
                                DateTimePicker::make('verified_at')
                                    ->label('Tanggal Verifikasi'),
                                TextInput::make('verified_by')
                                    ->label('Diverifikasi Oleh')
                                    ->disabled(),
                            ]),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->placeholder('Tambahkan catatan jika diperlukan...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

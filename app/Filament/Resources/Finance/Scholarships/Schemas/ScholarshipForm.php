<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finance\Scholarships\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class ScholarshipForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Beasiswa')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Beasiswa')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Beasiswa Bidikmisi, Beasiswa Yayasan A, dll'),

                        TextInput::make('code')
                            ->label('Kode Beasiswa')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('BM-2026, YA-FULL, dll')
                            ->helperText('Kode unik untuk referensi cepat'),

                        Select::make('funding_source')
                            ->label('Sumber Dana')
                            ->options([
                                'GOVERNMENT' => 'Pemerintah (KIP, Bidikmisi)',
                                'FOUNDATION' => 'Yayasan',
                                'INSTITUTION' => 'Internal Institusi',
                                'CORPORATE' => 'Perusahaan/Sponsor',
                            ])
                            ->required()
                            ->default('INSTITUTION'),

                        TextInput::make('coverage_percentage')
                            ->label('Persentase Coverage')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->suffix('%')
                            ->default(100)
                            ->required()
                            ->helperText('100 = Full coverage, 50 = Setengah SPP'),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->placeholder('Deskripsi syarat dan ketentuan beasiswa...'),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Jika nonaktif, beasiswa tidak bisa di-assign ke mahasiswa baru'),
                    ])
                    ->columns(2),
            ]);
    }
}

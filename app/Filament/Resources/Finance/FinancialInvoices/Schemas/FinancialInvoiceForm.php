<?php

namespace App\Filament\Resources\Finance\FinancialInvoices\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class FinancialInvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Tagihan')
                    ->columnSpanFull()
                    ->description('Detail utama tagihan mahasiswa')
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label('Nomor Invoice')
                            ->default('INV/' . date('Y/m/') . rand(1000, 9999))
                            ->required()
                            ->readOnly(), // Auto-generated usually

                        Select::make('id_registrasi_mahasiswa')
                            ->label('Mahasiswa')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) {
                                return \App\Models\RiwayatPendidikan::query()
                                    ->with('mahasiswa')
                                    ->where(function ($query) use ($search) {
                                        $query->whereHas('mahasiswa', function ($q) use ($search) {
                                            $q->where('nama_lengkap', 'like', "%{$search}%");
                                        })
                                            ->orWhere('nim', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function ($record) {
                                        return [$record->id_registrasi_mahasiswa => "{$record->mahasiswa?->nama_lengkap} - {$record->nim}"];
                                    });
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $record = \App\Models\RiwayatPendidikan::with('mahasiswa')
                                    ->where('id_registrasi_mahasiswa', $value)
                                    ->first();
                                return $record ? "{$record->mahasiswa?->nama_lengkap} - {$record->nim}" : null;
                            })
                            ->required()
                            ->disabledOn('edit')
                            ->placeholder('Cari Nama Mahasiswa...'),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('period_date')
                                    ->label('Periode Tagihan')
                                    ->required()
                                    ->displayFormat('d F Y'),

                                DatePicker::make('due_date')
                                    ->label('Jatuh Tempo')
                                    ->required()
                                    ->displayFormat('d F Y'),
                            ]),

                        Select::make('status')
                            ->label('Status Pembayaran')
                            ->options([
                                'UNPAID' => 'Belum Lunas',
                                'PAID' => 'Lunas',
                            ])
                            ->default('UNPAID')
                            ->required()
                            ->live(), // Reactive to show paid_at

                        DateTimePicker::make('paid_at')
                            ->label('Tanggal Lunas')
                            ->visible(fn(Get $get) => $get('status') === 'PAID'),
                    ])->columns(2),
                Section::make('Rincian Biaya')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                TextInput::make('component_name')
                                    ->label('Nama Komponen')
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('amount')
                                    ->label('Nominal')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required(),
                            ])
                            ->columns(3)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                // Auto-calculate total
                                $items = $get('items');
                                $total = collect($items)->sum('amount');
                                $set('total_amount', $total);
                            }),
                    ]),

                Section::make('Total')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('total_amount')
                            ->label('Total Tagihan')
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly()
                            ->required(),

                        DateTimePicker::make('generated_at')
                            ->label('Dibuat Pada')
                            ->default(now())
                            ->readOnly(),
                    ])->columns(2),
            ]);
    }
}

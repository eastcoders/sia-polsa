<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finance\StudentScholarships\Schemas;

use Filament\Schemas\Schema;
use App\Models\RiwayatPendidikan;
use App\Models\Finance\Scholarship;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use App\Enums\Finance\ScholarshipStatus;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;

class StudentScholarshipForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Penerima')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('id_registrasi_mahasiswa')
                            ->label('Mahasiswa')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(function () {
                                return RiwayatPendidikan::query()
                                    ->whereHas('aktivitasKuliahMahasiswa', function ($query) {
                                        $query->where('id_status_mahasiswa', 'A'); // Aktif
                                    })
                                    ->with('mahasiswa')
                                    ->get()
                                    ->mapWithKeys(function ($rp) {
                                        $nama = $rp->mahasiswa?->nama_lengkap ?? 'Unknown';
                                        return [$rp->id_registrasi_mahasiswa => "{$rp->nim} - {$nama}"];
                                    });
                            })
                            ->helperText('Cari berdasarkan NIM atau Nama'),

                        Select::make('scholarship_id')
                            ->label('Beasiswa')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->relationship('scholarship', 'name', fn($query) => $query->where('is_active', true))
                            ->getOptionLabelFromRecordUsing(fn(Scholarship $record) => "{$record->code} - {$record->name} ({$record->coverage_percentage}%)")
                            ->helperText('Pilih jenis beasiswa yang akan diberikan'),
                    ])
                    ->columns(2),

                Section::make('Periode Beasiswa')
                    ->columnSpanFull()
                    ->schema([
                        DatePicker::make('valid_from')
                            ->label('Mulai Berlaku')
                            ->required()
                            ->default(now()->startOfMonth())
                            ->helperText('Tanggal mulai beasiswa berlaku'),

                        DatePicker::make('valid_until')
                            ->label('Berakhir')
                            ->nullable()
                            ->afterOrEqual('valid_from')
                            ->helperText('Kosongkan jika berlaku sampai lulus'),

                        Select::make('coverage_type')
                            ->label('Tipe Coverage')
                            ->options([
                                'FULL_TUITION' => 'SPP Penuh',
                                'PARTIAL_TUITION' => 'SPP Sebagian',
                                'TUITION_AND_LIVING' => 'SPP + Biaya Hidup',
                            ])
                            ->default('FULL_TUITION')
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->options(
                                collect(ScholarshipStatus::cases())
                                    ->mapWithKeys(fn($status) => [$status->value => $status->label()])
                            )
                            ->default(ScholarshipStatus::ACTIVE->value)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Catatan')
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan Tambahan')
                            ->rows(3)
                            ->placeholder('Catatan internal tentang beasiswa ini...'),
                    ]),
            ]);
    }
}

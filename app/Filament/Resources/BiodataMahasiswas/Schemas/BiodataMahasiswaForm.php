<?php

namespace App\Filament\Resources\BiodataMahasiswas\Schemas;

use App\Models\Agama;
use App\Models\AlatTransportasi;
use App\Models\JenisTinggal;
use App\Models\JenjangPendidikan;
use App\Models\Pekerjaan;
use App\Models\Penghasilan;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class BiodataMahasiswaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // === DETAIL MAHASISWA ===
                Section::make('Detail Mahasiswa')
                    ->description('Informasi dasar mahasiswa')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])
                            ->schema([
                                TextInput::make('nama_lengkap')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled(fn($record) => $record->id_server != null),
                                Select::make('jenis_kelamin')
                                    ->options(['L' => 'Laki-laki', 'P' => 'Perempuan'])
                                    ->required(),
                                Select::make('id_agama')
                                    ->label('Agama')
                                    ->options(fn() => Agama::orderBy('id_agama')->pluck('nama_agama', 'id_agama'))
                                    ->searchable()
                                    ->required(),
                                DatePicker::make('tanggal_lahir')
                                    ->required()
                                    ->disabled(fn($record) => $record->id_server != null),
                                TextInput::make('tempat_lahir')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('nik')
                                    ->label('NIK')
                                    ->required()
                                    ->minLength(16)
                                    ->maxLength(16),
                                TextInput::make('nisn')
                                    ->label('NISN')
                                    ->maxLength(10)
                                    ->required(),
                                TextInput::make('npwp')
                                    ->label('NPWP')
                                    ->maxLength(15),
                                Select::make('penerima_kps')
                                    ->label('Penerima KPS')
                                    ->options([
                                        '0' => 'Tidak',
                                        '1' => 'Ya',
                                    ])
                                    ->required()
                                    ->reactive(),
                                TextInput::make('no_kps')
                                    ->label('Nomor KPS')
                                    ->required(fn($get) => $get('penerima_kps') == '1')
                                    ->visible(fn($get) => $get('penerima_kps') == '1'),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Kontak & Info Tambahan')
                    ->schema([
                        TextInput::make('telepone')
                            ->label('Telepon'),
                        TextInput::make('no_hp')
                            ->label('No. HP')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('ALAMAT MAHASISWA')
                            ->schema([
                                Section::make('Alamat Mahasiswa')
                                    ->description('Alamat tempat tinggal mahasiswa')
                                    ->schema([
                                        Select::make('kewarganegaraan')
                                            ->label('Kewarganegaraan')
                                            ->options([
                                                'Indonesia' => 'Warga Negara Indonesia',
                                            ])
                                            ->default('ID')
                                            ->required(),
                                        TextInput::make('kelurahan')
                                            ->required()
                                            ->maxLength(255),

                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('jalan')
                                                    ->maxLength(255),
                                                TextInput::make('rt')
                                                    ->label('RT')
                                                    ->maxLength(3),
                                                TextInput::make('rw')
                                                    ->label('RW')
                                                    ->maxLength(3),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('dusun')
                                                    ->maxLength(255),
                                                TextInput::make('kode_pos')
                                                    ->label('Kode Pos')
                                                    ->numeric()
                                                    ->maxLength(10),
                                            ]),
                                        Select::make('id_provinsi')
                                            ->label('Provinsi')
                                            ->required()
                                            ->options(\App\Models\Wilayah::where('id_level_wilayah', 1)
                                                ->orderBy('id_wilayah')
                                                ->pluck('nama_wilayah', 'id_wilayah')
                                                ->mapWithKeys(fn($value, $key) => [trim($key) => $value])
                                                ->toArray())
                                            ->searchable()
                                            ->live()
                                            ->dehydrated(false)
                                            ->afterStateUpdated(fn(callable $set) => $set('id_kabupaten', null)),

                                        Select::make('id_kabupaten')
                                            ->label('Kabupaten')
                                            ->required()
                                            ->options(fn(callable $get) => \App\Models\Wilayah::where('id_level_wilayah', 2)
                                                ->where('id_induk_wilayah', $get('id_provinsi'))
                                                ->orderBy('id_wilayah')
                                                ->pluck('nama_wilayah', 'id_wilayah')
                                                ->mapWithKeys(fn($value, $key) => [trim($key) => $value])
                                                ->toArray())
                                            ->searchable()
                                            ->live()
                                            ->dehydrated(false)
                                            ->disabled(fn(callable $get) => !$get('id_provinsi'))
                                            ->afterStateUpdated(fn(callable $set) => $set('id_kecamatan', null)),

                                        Select::make('id_wilayah')
                                            ->label('Kecamatan')
                                            ->required()
                                            ->options(function (callable $get) {
                                                $kabupatenId = $get('id_kabupaten');
                                                if (!$kabupatenId)
                                                    return [];

                                                $options = \App\Models\Wilayah::where('id_level_wilayah', 3)
                                                    ->where('id_induk_wilayah', $kabupatenId)
                                                    ->orderBy('id_wilayah')
                                                    ->pluck('nama_wilayah', 'id_wilayah')
                                                    ->mapWithKeys(fn($value, $key) => [trim($key) => $value])
                                                    ->toArray();

                                                $currentVal = $get('id_wilayah');
                                                if ($currentVal && !isset($options[trim($currentVal)])) {
                                                    $extra = \App\Models\Wilayah::where('id_wilayah', trim($currentVal))->first();
                                                    if ($extra) {
                                                        $options[trim($extra->id_wilayah)] = $extra->nama_wilayah;
                                                    }
                                                }
                                                return $options;
                                            })
                                            ->searchable()
                                            ->live()
                                            ->disabled(fn(callable $get) => !$get('id_kabupaten')),

                                        Select::make('id_jenis_tinggal')
                                            ->label('Jenis Tinggal')
                                            ->options(fn() => JenisTinggal::orderBy('id_jenis_tinggal')->pluck('nama_jenis_tinggal', 'id_jenis_tinggal'))
                                            ->searchable(),
                                        Select::make('id_alat_transportasi')
                                            ->label('Alat Transportasi')
                                            ->options(fn() => AlatTransportasi::orderBy('id_alat_transportasi')->pluck('nama_alat_transportasi', 'id_alat_transportasi'))
                                            ->searchable(),
                                    ])
                                    ->columns(2),
                            ]),
                        Tab::make('ORANG TUA')
                            ->schema([
                                Section::make('Informasi Orang Tua')
                                    ->description('Data ayah dan ibu kandung mahasiswa')
                                    ->schema([
                                        // Ibu
                                        TextInput::make('nama_ibu_kandung')
                                            ->label('Nama Ibu Kandung')
                                            ->required()
                                            ->disabled(fn($record) => $record->id_server != null)
                                            ->maxLength(255),
                                        DatePicker::make('tanggal_lahir_ibu')
                                            ->label('Tanggal Lahir Ibu'),
                                        TextInput::make('nik_ibu')
                                            ->label('NIK Ibu')
                                            ->maxLength(16),
                                        Select::make('id_pekerjaan_ibu')
                                            ->label('Pekerjaan Ibu')
                                            ->options(fn() => Pekerjaan::orderBy('id_pekerjaan')->pluck('nama_pekerjaan', 'id_pekerjaan'))
                                            ->searchable(),
                                        Select::make('id_penghasilan_ibu')
                                            ->label('Penghasilan Ibu')
                                            ->options(fn() => Penghasilan::orderBy('id_penghasilan')->pluck('nama_penghasilan', 'id_penghasilan'))
                                            ->searchable(),
                                        Select::make('id_pendidikan_ibu')
                                            ->label('Pendidikan Ibu')
                                            ->options(fn() => JenjangPendidikan::orderBy('id_jenjang_didik')->pluck('nama_jenjang_didik', 'id_jenjang_didik'))
                                            ->searchable(),

                                        // Ayah
                                        TextInput::make('nama_ayah')
                                            ->label('Nama Ayah')
                                            ->maxLength(255),
                                        DatePicker::make('tanggal_lahir_ayah')
                                            ->label('Tanggal Lahir Ayah'),
                                        TextInput::make('nik_ayah')
                                            ->label('NIK Ayah')
                                            ->maxLength(16),
                                        Select::make('id_pekerjaan_ayah')
                                            ->label('Pekerjaan Ayah')
                                            ->options(fn() => Pekerjaan::orderBy('id_pekerjaan')->pluck('nama_pekerjaan', 'id_pekerjaan'))
                                            ->searchable(),
                                        Select::make('id_penghasilan_ayah')
                                            ->label('Penghasilan Ayah')
                                            ->options(fn() => Penghasilan::orderBy('id_penghasilan')->pluck('nama_penghasilan', 'id_penghasilan'))
                                            ->searchable(),
                                        Select::make('id_pendidikan_ayah')
                                            ->label('Pendidikan Ayah')
                                            ->options(fn() => JenjangPendidikan::orderBy('id_jenjang_didik')->pluck('nama_jenjang_didik', 'id_jenjang_didik'))
                                            ->searchable(),
                                    ])
                                    ->columns(2),
                            ]),
                        Tab::make('WALI')
                            ->schema([
                                Section::make('Informasi Wali')
                                    ->description('Data wali (jika tidak tinggal dengan orang tua)')
                                    ->schema([
                                        TextInput::make('nama_wali')
                                            ->label('Nama Wali')
                                            ->maxLength(255),
                                        TextInput::make('nik_wali')
                                            ->label('NIK Wali')
                                            ->maxLength(16),
                                        Select::make('id_pekerjaan_wali')
                                            ->label('Pekerjaan Wali')
                                            ->options(fn() => Pekerjaan::orderBy('id_pekerjaan')->pluck('nama_pekerjaan', 'id_pekerjaan'))
                                            ->searchable(),
                                        Select::make('id_penghasilan_wali')
                                            ->label('Penghasilan Wali')
                                            ->options(fn() => Penghasilan::orderBy('id_penghasilan')->pluck('nama_penghasilan', 'id_penghasilan'))
                                            ->searchable(),
                                        Select::make('id_pendidikan_wali')
                                            ->label('Pendidikan Wali')
                                            ->options(fn() => JenjangPendidikan::orderBy('id_jenjang_didik')->pluck('nama_jenjang_didik', 'id_jenjang_didik'))
                                            ->searchable(),
                                    ])
                                    ->columns(2),
                            ]),
                    ]),
            ]);
    }
}

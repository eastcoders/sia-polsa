<?php

namespace App\Filament\Dosen\Resources\FormTemplates\Schemas;

use Filament\Schemas\Schema;

class FormTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('title')
                            ->label('Judul Kuesioner')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\Select::make('category')
                            ->options([
                                'UTS_LAYANAN' => 'UTS (Layanan Akademik)',
                                'UAS_DOSEN' => 'UAS (Kinerja Dosen)'
                            ])
                            ->required(),
                        \Filament\Forms\Components\Select::make('evaluation_target')
                            ->label('Target Evaluasi')
                            ->options([
                                'App\Models\Dosen' => 'Dosen (Per Matakuliah)',
                                'App\Models\Prodi' => 'Program Studi',
                                'App\Models\Biro' => 'Unit Layanan / Biro'
                            ])
                            ->required(),
                        \Filament\Forms\Components\Select::make('semester_id')
                            ->relationship('semester', 'nama_semester', fn($query) => $query->orderBy('id_tahun_ajaran', 'desc'))
                            ->required(),
                        \Filament\Forms\Components\Toggle::make('is_active')
                            ->label('Aktif / Publikasi')
                    ])->columns(2),

                \Filament\Forms\Components\Section::make('Desainer Soal')
                    ->description('Atur daftar pertanyaan yang akan muncul pada kuesioner ini.')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('schema_snapshot')
                            ->label('Daftar Pertanyaan')
                            ->schema([
                                \Filament\Forms\Components\Textarea::make('text')
                                    ->label('Teks Pertanyaan')
                                    ->required()
                                    ->rows(2),
                                \Filament\Forms\Components\Select::make('type')
                                    ->options([
                                        'scale' => 'Skala Likert (1-4)',
                                        'choice' => 'Pilihan Ganda Custom',
                                        'essay' => 'Esai / Masukan Teks'
                                    ])
                                    ->required()
                                    ->reactive(),

                                \Filament\Forms\Components\TextInput::make('metric_key')
                                    ->label('Kode Metrik Laporan')
                                    ->placeholder('misal: kedisiplinan_waktu')
                                    ->helperText('Wajib diisi untuk tipe Skala agar bisa direkap grafiknya.')
                                    ->visible(fn($get) => $get('type') === 'scale')
                                    ->required(fn($get) => $get('type') === 'scale'),

                                \Filament\Forms\Components\Repeater::make('options')
                                    ->label('Opsi Jawaban')
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('label')->required(),
                                        \Filament\Forms\Components\TextInput::make('value')
                                            ->label('Bobot Nilai')
                                            ->numeric()
                                            ->default(0)
                                            ->required(),
                                    ])
                                    ->columns(2)
                                    ->visible(fn($get) => $get('type') === 'choice')
                            ])
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['text'] ?? null),
                    ])
            ]);
    }
}

<?php

namespace App\Filament\Resources\JadwalUjians;

use UnitEnum;
use BackedEnum;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\JadwalUjian;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Filament\Resources\JadwalUjians\Pages\EditJadwalUjian;
use App\Filament\Resources\JadwalUjians\Pages\ListJadwalUjians;
use App\Filament\Resources\JadwalUjians\Pages\CreateJadwalUjian;

class JadwalUjianResource extends Resource
{
    protected static ?string $model = JadwalUjian::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string|UnitEnum|null $navigationGroup = 'Akademik';
    protected static ?string $navigationLabel = 'Jadwal Ujian';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Dasar')
                    ->schema([
                        Select::make('id_kelas_kuliah')
                            ->label('Mata Kuliah')
                            ->relationship('kelasKuliah', 'id_kelas_kuliah')
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->matkul->nama_mata_kuliah . ' - ' . $record->nama_kelas_kuliah)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('jenis_ujian')
                            ->options([
                                'UTS' => 'UTS',
                                'UAS' => 'UAS',
                            ])
                            ->required(),
                        Select::make('mode_ujian')
                            ->options([
                                'ONSITE' => 'On-Site (Tulis/Lab)',
                                'TAKE_HOME' => 'Take Home (Proyek/Paper)',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn($state, Set $set) => $state === 'TAKE_HOME' ? $set('id_ruang', null) : null),
                    ])->columns(2),

                Section::make('Detail Pelaksanaan')
                    ->schema([
                        // On-Site Fields
                        DatePicker::make('tanggal_ujian')
                            ->label('Tanggal Ujian')
                            ->visible(fn(Get $get) => $get('mode_ujian') === 'ONSITE')
                            ->required(fn(Get $get) => $get('mode_ujian') === 'ONSITE'),
                        TimePicker::make('jam_mulai')
                            ->visible(fn(Get $get) => $get('mode_ujian') === 'ONSITE')
                            ->required(fn(Get $get) => $get('mode_ujian') === 'ONSITE'),
                        TimePicker::make('jam_selesai')
                            ->visible(fn(Get $get) => $get('mode_ujian') === 'ONSITE')
                            ->required(fn(Get $get) => $get('mode_ujian') === 'ONSITE'),
                        Select::make('id_ruang')
                            ->label('Ruangan')
                            ->relationship('ruangKelas', 'nama_ruang_kelas')
                            ->visible(fn(Get $get) => $get('mode_ujian') === 'ONSITE')
                            ->required(fn(Get $get) => $get('mode_ujian') === 'ONSITE'),

                        // Take-Home Fields
                        DateTimePicker::make('deadline_submission')
                            ->label('Batas Pengumpulan')
                            ->visible(fn(Get $get) => $get('mode_ujian') === 'TAKE_HOME')
                            ->required(fn(Get $get) => $get('mode_ujian') === 'TAKE_HOME'),
                        Textarea::make('submission_link')
                            ->label('Link/Instruksi Pengumpulan')
                            ->visible(fn(Get $get) => $get('mode_ujian') === 'TAKE_HOME')
                            ->columnSpanFull(),
                    ])->columns(2),

                Toggle::make('is_published')
                    ->label('Terbitkan Jadwal')
                    ->helperText('Jika aktif, mahasiswa dapat melihat jadwal ini di kartu ujian.')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kelasKuliah.matkul.nama_resmi')
                    ->label('Mata Kuliah')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kelasKuliah.nama_kelas')
                    ->label('Kelas'),
                TextColumn::make('jenis_ujian')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'UTS' => 'info',
                        'UAS' => 'warning',
                    }),
                TextColumn::make('mode_ujian')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ONSITE' => 'success',
                        'TAKE_HOME' => 'gray',
                    }),
                TextColumn::make('waktu_pelaksanaan')
                    ->label('Waktu / Deadline')
                    ->getStateUsing(function ($record) {
                        if ($record->mode_ujian === 'ONSITE') {
                            return $record->tanggal_ujian?->format('d M Y') . ' ' . $record->jam_mulai?->format('H:i');
                        }
                        return $record->deadline_submission?->format('d M Y H:i') . ' (Deadline)';
                    }),
                IconColumn::make('is_published')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('jenis_ujian')
                    ->options([
                        'UTS' => 'UTS',
                        'UAS' => 'UAS',
                    ]),
                SelectFilter::make('mode_ujian')
                    ->options([
                        'ONSITE' => 'On-Site',
                        'TAKE_HOME' => 'Take Home',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJadwalUjians::route('/'),
            'create' => CreateJadwalUjian::route('/create'),
            'edit' => EditJadwalUjian::route('/{record}/edit'),
        ];
    }
}

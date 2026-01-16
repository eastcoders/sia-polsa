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
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\JadwalUjians\Pages\EditJadwalUjian;
use App\Filament\Resources\JadwalUjians\Pages\ListJadwalUjians;
use App\Filament\Resources\JadwalUjians\Pages\CreateJadwalUjian;

class JadwalUjianResource extends Resource
{
    protected static ?string $model = JadwalUjian::class;

    protected static string|UnitEnum|null $navigationGroup = 'Akademik';
    protected static ?string $navigationLabel = 'Jadwal Ujian';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Dasar')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('filter_semester')
                            ->label('Filter Semester')
                            ->columnSpanFull()
                            ->options(fn() => \App\Models\Semester::where('a_periode_aktif', '1')
                                ->orderBy('id_semester', 'desc')
                                ->pluck('nama_semester', 'id_semester'))
                            ->default(fn() => session('active_semester_id')
                                ?? \App\Models\Semester::where('a_periode_aktif', 1)->value('id_semester'))
                            ->afterStateHydrated(function (Set $set, Get $get, $record) {
                                // On edit, set filter_semester from the loaded record's kelasKuliah
                                if ($record && $record->kelasKuliah) {
                                    $set('filter_semester', $record->kelasKuliah->id_semester);
                                }
                            })
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('id_kelas_kuliah', null))
                            ->dehydrated(false)
                            ->native(false)
                            ->helperText('Filter ini hanya untuk mempermudah pencarian, tidak disimpan ke database.'),
                        Select::make('id_kelas_kuliah')
                            ->label('Mata Kuliah')
                            ->relationship(
                                'kelasKuliah',
                                'nama_kelas_kuliah',
                                modifyQueryUsing: fn(Builder $query, Get $get) => $query
                                    ->where('id_semester', $get('filter_semester'))
                                    ->orderBy('nama_kelas_kuliah', 'asc'),
                            )
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->matkul->nama_mata_kuliah . ' - ' . $record->nama_kelas_kuliah)
                            ->getSearchResultsUsing(function (string $search, Get $get): array {
                                return \App\Models\KelasKuliah::query()
                                    ->where('id_semester', $get('filter_semester'))
                                    ->where(function (Builder $query) use ($search) {
                                        $query->where('nama_kelas_kuliah', 'like', "%{$search}%")
                                            ->orWhereHas(
                                                'matkul',
                                                fn(Builder $q) =>
                                                $q->where('nama_mata_kuliah', 'like', "%{$search}%")
                                            );
                                    })
                                    ->with('matkul')
                                    ->orderBy('nama_kelas_kuliah', 'asc')
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn($record) => [
                                        $record->id_kelas_kuliah => $record->matkul->nama_mata_kuliah . ' - ' . $record->nama_kelas_kuliah
                                    ])
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value): ?string {
                                $kelas = \App\Models\KelasKuliah::with('matkul')
                                    ->where('id_kelas_kuliah', $value)
                                    ->first();
                                return $kelas?->matkul?->nama_mata_kuliah . ' - ' . $kelas?->nama_kelas_kuliah;
                            })
                            ->searchable()
                            ->columnSpanFull()
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
                            ->seconds(false)
                            ->required(fn(Get $get) => $get('mode_ujian') === 'ONSITE'),
                        TimePicker::make('jam_selesai')
                            ->visible(fn(Get $get) => $get('mode_ujian') === 'ONSITE')
                            ->seconds(false)
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
                TextColumn::make('kelasKuliah.matkul.nama_mata_kuliah')
                    ->label('Mata Kuliah')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kelasKuliah.nama_kelas_kuliah')
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
                    ])
                    ->default(fn() => \App\Services\ExamPeriodService::getActiveExamType())
                    ->placeholder('Semua Jenis'),
                SelectFilter::make('semester')
                    ->relationship('kelasKuliah.semester', 'nama_semester')
                    ->searchable()
                    ->preload()
                    ->default(fn() => session('active_semester_id')
                        ?? \App\Models\Semester::where('a_periode_aktif', 1)->value('id_semester'))
                    ->placeholder('Semua Semester'),
                SelectFilter::make('mode_ujian')
                    ->options([
                        'ONSITE' => 'On-Site',
                        'TAKE_HOME' => 'Take Home',
                    ])
                    ->placeholder('Semua Mode'),
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

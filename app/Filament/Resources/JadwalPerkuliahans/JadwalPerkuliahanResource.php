<?php

namespace App\Filament\Resources\JadwalPerkuliahans;

use Closure;
use UnitEnum;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use App\Models\JadwalPerkuliahan;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TimePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Enums\RecordActionsPosition;
use App\Filament\Resources\JadwalPerkuliahans\Pages\ManageJadwalPerkuliahans;

class JadwalPerkuliahanResource extends Resource
{
    protected static ?string $model = JadwalPerkuliahan::class;

    protected static string|UnitEnum|null $navigationGroup = 'Perkuliahan';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('id_kelas_kuliah')
                    ->label('Mata Kuliah')
                    ->relationship(
                        'kelasKuliah',
                        'nama_kelas_kuliah',
                        modifyQueryUsing: fn(Builder $query, Get $get) => $query->where('id_semester', $get('id_semester')),
                    )
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->nama_kelas_kuliah} - {$record->matkul->nama_mata_kuliah}")
                    ->required()
                    ->columnSpanFull()
                    ->native(false),
                Select::make('id_ruang')
                    ->relationship('ruangKelas', 'nama_ruang_kelas')
                    ->required()
                    ->columnSpanFull()
                    ->native(false),
                Select::make('id_semester')
                    ->relationship(
                        'semester',
                        'nama_semester',
                        modifyQueryUsing: fn(Builder $query) => $query->where('a_periode_aktif', 1)->orderBy('nama_semester', 'desc'),
                    )
                    ->default(fn() => session('active_semester_id') ?? \App\Models\Semester::where('a_periode_aktif', 1)->value('id_semester'))
                    ->required()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(fn(Set $set) => $set('id_kelas_kuliah', null)),
                Select::make('hari')
                    ->required()
                    ->native(false)
                    ->options([
                        'senin' => 'Senin',
                        'selasa' => 'Selasa',
                        'rabu' => 'Rabu',
                        'kamis' => 'Kamis',
                        'jumat' => 'Jumat',
                    ]),
                TimePicker::make('jam_mulai')
                    ->required()
                    ->seconds(false)
                    ->timezone('Asia/Jakarta'),
                TimePicker::make('jam_selesai')
                    ->required()
                    ->seconds(false)
                    ->timezone('Asia/Jakarta')
                    ->rule(function (Get $get, ?JadwalPerkuliahan $record) {
                        return function (string $attribute, $value, Closure $fail) use ($get, $record) {
                            $ruang = $get('id_ruang');
                            $hari = $get('hari');
                            $jamMulai = $get('jam_mulai');
                            $kelas = $get('id_kelas_kuliah');

                            if (!$ruang || !$hari || !$jamMulai || !$value) {
                                return;
                            }

                            // Cek Bentrok Ruangan
                            $bentrok = JadwalPerkuliahan::query()
                                ->where('id_ruang', $ruang)
                                ->where('hari', $hari)
                                ->where(function ($query) use ($jamMulai, $value) {
                                $query->where('jam_mulai', '<', $value)
                                    ->where('jam_selesai', '>', $jamMulai);
                            })
                                ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                                ->exists();

                            if ($bentrok) {
                                $fail('Jadwal bentrok: Ruangan sudah terpakai pada waktu tersebut.');

                                return;
                            }

                            // Cek Duplikat Persis
                            $duplikat = JadwalPerkuliahan::query()
                                ->where('id_kelas_kuliah', $kelas)
                                ->where('id_ruang', $ruang)
                                ->where('hari', $hari)
                                ->where('jam_mulai', $jamMulai)
                                ->where('jam_selesai', $value)
                                ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                                ->exists();

                            if ($duplikat) {
                                $fail('Data jadwal duplikat sudah ada.');
                            }
                        };
                    }),
                Select::make('kelas_pagi_sore')
                    ->label('Pilih Kelas Pagi/Sore')
                    ->columnSpanFull()
                    ->options([
                        'pagi' => 'Pagi',
                        'sore' => 'Sore',
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('No.')
                    ->rowIndex()
                    ->searchable(),
                TextColumn::make('kelasKuliah.prodi.nama_program_studi')
                    ->label('Program Studi')
                    ->searchable()
                    ->formatStateUsing(fn(string $state): string => __(Str::upper($state))),
                TextColumn::make('kelasKuliah.matkul.nama_mata_kuliah')
                    ->label('Mata Kuliah')
                    ->searchable(),
                TextColumn::make('kelasKuliah.nama_kelas_kuliah')
                    ->label('Kelas')
                    ->searchable()
                    ->formatStateUsing(fn($record, $state) => $state . ' - ' . Str::ucfirst($record->kelas_pagi_sore)),
                TextColumn::make('hari')
                    ->searchable()
                    ->formatStateUsing(fn(string $state): string => __(Str::ucfirst($state))),
                TextColumn::make('ruangKelas.nama_ruang_kelas')
                    ->label('Ruangan')
                    ->searchable(),
                TextColumn::make('jam_mulai')
                    ->time("H:i"),
                TextColumn::make('jam_selesai')
                    ->time("H:i"),
                TextColumn::make('semester.nama_semester')
                    ->label('Semester')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('semester')
                    ->relationship('semester', 'nama_semester')
                    ->label('Semester')
                    ->default(fn() => session('active_semester_id') ?? \App\Models\Semester::where('a_periode_aktif', 1)->value('id_semester'))
                    ->preload()
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->modalWidth(Width::Medium)
                    ->iconButton()
                    ->tooltip('Edit Data'),
                ViewAction::make()
                    ->slideOver()
                    ->modalWidth(Width::Medium)
                    ->iconButton()
                    ->tooltip('View Data'),
                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Delete Data'),
            ], RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageJadwalPerkuliahans::route('/'),
        ];
    }
}

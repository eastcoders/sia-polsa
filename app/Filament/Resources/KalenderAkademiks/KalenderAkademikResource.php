<?php

namespace App\Filament\Resources\KalenderAkademiks;

use App\Filament\Resources\KalenderAkademiks\Pages\ManageKalenderAkademiks;
use App\Models\KalenderAkademik;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KalenderAkademikResource extends Resource
{
    protected static ?string $model = KalenderAkademik::class;

    protected static string|UnitEnum|null $navigationGroup = 'Akademik';


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('tanggal_mulai')
                    ->required(),
                DatePicker::make('tanggal_selesai')
                    ->required(),
                TextInput::make('keterangan')
                    ->required(),
                \Filament\Forms\Components\Select::make('jenis_kegiatan')
                    ->label('Jenis Kegiatan')
                    ->options([
                        'MINGGU_UTS' => 'Minggu UTS',
                        'MINGGU_UAS' => 'Minggu UAS',
                        'PERKULIAHAN' => 'Perkuliahan',
                        'LIBUR_SEMESTER' => 'Libur Semester',
                        'LIBUR_NASIONAL' => 'Libur Nasional',
                        'KEGIATAN_AKADEMIK' => 'Kegiatan Akademik',
                    ])
                    ->native(false)
                    ->searchable(),
                \Filament\Forms\Components\Select::make('id_semester')
                    ->label('Semester')
                    ->searchable()
                    ->options(\App\Models\Semester::where('a_periode_aktif', '1')->orderBy('id_tahun_ajaran', 'desc')->pluck('nama_semester', 'id_semester'))
                    ->preload(),
                Toggle::make('is_libur')
                    ->required(),
                Toggle::make('is_minggu_ujian')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal_mulai')
                    ->date()
                    ->sortable(),
                TextColumn::make('tanggal_selesai')
                    ->date()
                    ->sortable(),
                TextColumn::make('keterangan')
                    ->searchable(),
                TextColumn::make('jenis_kegiatan')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'MINGGU_UTS' => 'info',
                        'MINGGU_UAS' => 'warning',
                        'PERKULIAHAN' => 'success',
                        'LIBUR_SEMESTER', 'LIBUR_NASIONAL' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'MINGGU_UTS' => 'Minggu UTS',
                        'MINGGU_UAS' => 'Minggu UAS',
                        'PERKULIAHAN' => 'Perkuliahan',
                        'LIBUR_SEMESTER' => 'Libur Semester',
                        'LIBUR_NASIONAL' => 'Libur Nasional',
                        'KEGIATAN_AKADEMIK' => 'Kegiatan Akademik',
                        default => $state ?? '-',
                    }),
                IconColumn::make('is_libur')
                    ->boolean(),
                IconColumn::make('is_minggu_ujian')
                    ->boolean(),
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
            ->defaultSort('tanggal_mulai')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageKalenderAkademiks::route('/'),
        ];
    }
}

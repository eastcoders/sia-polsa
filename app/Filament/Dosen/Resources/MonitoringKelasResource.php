<?php

namespace App\Filament\Dosen\Resources;

use App\Filament\Dosen\Resources\MonitoringKelasResource\Pages;
use App\Models\KelasKuliah;
use App\Traits\KaprodiScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Schemas\Schema;
use UnitEnum;
use Filament\Actions\ViewAction;

class MonitoringKelasResource extends Resource
{
    // 1. Integrasi Security Scope
    use KaprodiScope;

    /**
     * Pastikan hanya Kaprodi yang bisa melihat menu ini.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('kaprodi') ?? false;
    }

    protected static ?string $model = KelasKuliah::class;

    // 2. Grouping Navigation
    protected static string|UnitEnum|null $navigationGroup = 'Fitur Kaprodi';

    protected static ?string $navigationLabel = 'Monitoring Kelas';

    // protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Form view (Read only mostly for monitoring)
                Forms\Components\Select::make('matkul.nama_mata_kuliah')
                    ->label('Mata Kuliah')
                    ->relationship('matkul', 'nama_mata_kuliah')
                    ->disabled(),
                Forms\Components\TextInput::make('nama_kelas_kuliah')
                    ->label('Kelas')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('matkul.nama_mata_kuliah')
                    ->label('Mata Kuliah')
                    ->searchable()
                    ->sortable()
                    ->description(fn(KelasKuliah $record) => $record->matkul?->kode_mata_kuliah),

                Tables\Columns\TextColumn::make('nama_kelas_kuliah')
                    ->label('Kelas')
                    ->searchable(),

                Tables\Columns\TextColumn::make('dosenPengajarKelasKuliah.dosen.user.name')
                    ->label('Dosen Pengajar')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->placeholder('Belum Ada Dosen')
                    ->color(fn($state) => empty($state) ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('jadwalPerkuliahan')
                    ->label('Jadwal')
                    ->formatStateUsing(fn($state, $record) => $record->jadwalPerkuliahan->map(function ($j) {
                        return "{$j->hari}, {$j->jam_mulai}-{$j->jam_selesai}";
                    })->join('<br>'))
                    ->html()
                    ->placeholder('Belum Ada Jadwal')
                    ->color(fn($record) => $record->jadwalPerkuliahan->isEmpty() ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('pertemuan_kelas_count')
                    ->counts('pertemuanKelas')
                    ->label('Realisasi Pertemuan')
                    ->badge()
                    ->color(fn($state) => $state < 4 ? 'warning' : 'success'),
            ])
            ->filters([
                // Filter Periode Semester (Default: Active)
                Tables\Filters\SelectFilter::make('id_semester')
                    ->label('Periode Semester')
                    ->relationship(
                        'semester',
                        'nama_semester',
                        fn(Builder $query) => $query->orderBy('nama_semester', 'desc')
                    )
                    ->default(fn() => \App\Models\Semester::where('a_periode_aktif', 1)->value('id_semester'))
                    ->searchable()
                    ->preload(),

                // Filter "Kelas Bermasalah" (Drill Down Target 1)
                Tables\Filters\Filter::make('kelas_bermasalah')
                    ->label('⚠️ Kelas Bermasalah (Hazard)')
                    ->query(fn(Builder $query) => $query->where(function ($q) {
                        $q->whereDoesntHave('dosenPengajarKelasKuliah')
                            ->orWhereDoesntHave('jadwalPerkuliahan');
                    })),

                // Filter "Low Attendance" (Drill Down Target 2)
                Tables\Filters\Filter::make('low_attendance')
                    ->label('📉 Low Performance (< 4 Pertemuan)')
                    ->query(fn(Builder $query) => $query->has('pertemuanKelas', '<', 4)),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('nama_kelas_kuliah', 'asc');
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
            'index' => Pages\ListMonitoringKelas::route('/'),
        ];
    }
}

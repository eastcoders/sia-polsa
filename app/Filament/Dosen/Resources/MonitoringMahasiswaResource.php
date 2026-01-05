<?php

namespace App\Filament\Dosen\Resources;

use App\Filament\Dosen\Resources\MonitoringMahasiswaResource\Pages;
use App\Models\AktivitasKuliahMahasiswa;
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

class MonitoringMahasiswaResource extends Resource
{
    use KaprodiScope;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('kaprodi') ?? false;
    }

    protected static ?string $model = AktivitasKuliahMahasiswa::class;

    protected static string|UnitEnum|null $navigationGroup = 'Fitur Kaprodi';

    protected static ?string $navigationLabel = 'Monitoring Mahasiswa';

    // protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Read Only View
                Forms\Components\TextInput::make('ipk')
                    ->label('IPK')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('riwayatPendidikan.mahasiswa.nama_mahasiswa')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->riwayatPendidikan?->mahasiswa?->nim),

                Tables\Columns\TextColumn::make('semester.nama_semester')
                    ->label('Periode')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ipk')
                    ->label('IPK')
                    ->badge()
                    ->color(fn($state) => $state < 2.00 ? 'danger' : 'success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ips')
                    ->label('IPS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('statusMahasiswa.nama_status_mahasiswa')
                    ->label('Status')
                    ->badge(),
            ])
            ->filters([
                // Filter "Mahasiswa Kritis" (Drill Down Target 3)
                Tables\Filters\Filter::make('mahasiswa_kritis')
                    ->label('🚨 Mahasiswa Kritis (IPK < 2.0)')
                    ->query(fn(Builder $query) => $query->where('ipk', '<', 2.00)->where('ipk', '>', 0)),

                Tables\Filters\SelectFilter::make('status')
                    ->relationship('statusMahasiswa', 'nama_status_mahasiswa')
                    ->label('Status Mahasiswa'),
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
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
            'index' => Pages\ListMonitoringMahasiswa::route('/'),
        ];
    }

    // Pastikan hanya menampilkan data semester aktif secara default? 
    // Atau serahkan ke Filter? User mungkin mau lihat history.
    // Tapi Trait KaprodiScope sudah membatasi Prodi.
}

<?php

namespace App\Filament\Dosen\Resources\AccKhs;

use BackedEnum;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\BulkAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Models\AktivitasKuliahMahasiswa;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Dosen\Resources\AccKhs\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccKhsResource extends Resource
{
    protected static ?string $model = AktivitasKuliahMahasiswa::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static string|\UnitEnum|null $navigationGroup = 'Validasi Akademik';

    protected static ?string $navigationLabel = 'ACC KHS Mahasiswa';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('kaprodi') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            // Form logic if needed (Read Only)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('riwayatPendidikan.mahasiswa.nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('riwayatPendidikan.mahasiswa.nama_lengkap')
                    ->label('Nama Mahasiswa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('semester.nama_semester')
                    ->label('Semester')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ips')
                    ->label('IPS')
                    ->numeric(2)
                    ->sortable(),
                Tables\Columns\TextColumn::make('ipk')
                    ->label('IPK')
                    ->numeric(2)
                    ->sortable(),
                Tables\Columns\IconColumn::make('khs_is_approved')
                    ->label('Status ACC')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('success')
                    ->falseColor('warning'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_semester')
                    ->relationship('semester', 'nama_semester')
                    ->default(fn() => \App\Models\Semester::where('a_periode_aktif', 1)->orderBy('id_semester', 'desc')->value('id_semester'))
                    ->label('Semester')
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('khs_is_approved')
                    ->label('Status Approval'),
            ])
            ->actions([
                Action::make('approve')
                    ->label('ACC KHS')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (AktivitasKuliahMahasiswa $record) {
                        $record->update([
                            'khs_is_approved' => true,
                            'khs_approved_at' => now(),
                            'khs_approved_by' => auth()->id(),
                        ]);
                        Notification::make()->title('KHS Berhasil di-ACC')->success()->send();
                    })
                    ->visible(fn(AktivitasKuliahMahasiswa $record) => !$record->khs_is_approved),

                Action::make('unlock')
                    ->label('Batal ACC')
                    ->icon('heroicon-o-key')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (AktivitasKuliahMahasiswa $record) {
                        $record->update([
                            'khs_is_approved' => false,
                            'khs_approved_at' => null,
                            'khs_approved_by' => null,
                        ]);
                        Notification::make()->title('ACC KHS Dibatalkan')->warning()->send();
                    })
                    ->visible(fn(AktivitasKuliahMahasiswa $record) => $record->khs_is_approved),
            ])
            ->bulkActions([
                BulkAction::make('approve_bulk')
                    ->label('ACC Terpilih')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                        foreach ($records as $record) {
                            $record->update([
                                'khs_is_approved' => true,
                                'khs_approved_at' => now(),
                                'khs_approved_by' => auth()->id(),
                            ]);
                        }
                        Notification::make()->title('KHS Berhasil di-ACC Massal')->success()->send();
                    }),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Scope Logic: Hanya tampilkan mahasiswa di Prodi milik Kaprodi
        // Relasi: AKM -> RiwayatPendidikan -> id_prodi
        $user = auth()->user();
        if ($user && $user->hasRole('kaprodi')) {
            $prodiIds = $user->dosen?->memimpinProdi?->pluck('id_prodi')->toArray() ?? [];
            if (!empty($prodiIds)) {
                $query->whereHas('riwayatPendidikan', function ($q) use ($prodiIds) {
                    $q->whereIn('id_prodi', $prodiIds);
                });
            }
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccKhs::route('/'),
        ];
    }
}

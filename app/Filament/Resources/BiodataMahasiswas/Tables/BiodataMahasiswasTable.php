<?php

namespace App\Filament\Resources\BiodataMahasiswas\Tables;

use App\Models\Prodi;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BiodataMahasiswasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable(),
                TextColumn::make('riwayatPendidikan.nim')
                    ->label('NIM')
                    ->searchable(),
                TextColumn::make('jenis_kelamin')
                    ->formatStateUsing(function ($state) {
                        return $state === 'L' ? 'Laki-laki' : 'Perempuan';
                    }),
                TextColumn::make('agama.nama_agama'),
                TextColumn::make('tanggal_lahir')
                    ->date('d F Y')
                    ->sortable(),
                TextColumn::make('riwayatPendidikan.prodi.programStudiLengkap')
                    ->label('Program Studi'),
                TextColumn::make('riwayatPendidikan.periodeDaftar.id_tahun_ajaran')
                    ->label('Angkatan'),
            ])
            ->filters([
                SelectFilter::make('prodi')
                    ->label('Program Studi')
                    ->options(
                        Prodi::orderBy('nama_jenjang_pendidikan')
                            ->orderBy('nama_program_studi')
                            ->pluck('nama_program_studi', 'id_prodi')
                            ->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['values'])) {
                            $query->whereHas('riwayatPendidikan', function (Builder $q) use ($data) {
                                $q->whereIn('id_prodi', $data['values']);
                            });

                        }

                        return $query;
                    })
                    ->multiple(),
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

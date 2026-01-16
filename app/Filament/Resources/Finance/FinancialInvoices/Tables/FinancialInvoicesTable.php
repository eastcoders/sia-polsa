<?php

namespace App\Filament\Resources\Finance\FinancialInvoices\Tables;

use App\Models\Prodi;
use App\Models\Semester;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FinancialInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Nomor Invoice')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('riwayatPendidikan.nim')
                    ->label('NIM')
                    ->searchable(),

                TextColumn::make('riwayatPendidikan.mahasiswa.nama_lengkap')
                    ->label('Mahasiswa')
                    ->searchable(),

                TextColumn::make('period_date')
                    ->label('Periode')
                    ->date('M Y')
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn($record) => $record->due_date < now() && $record->status === 'UNPAID' ? 'danger' : null),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state?->label() ?? $state)
                    ->color(fn($state) => $state?->color() ?? 'gray'),

                TextColumn::make('paid_at')
                    ->label('Dibayar Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('generated_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('invoice_number', 'asc')
            ->filters([
                SelectFilter::make('angkatan')
                    ->label('Tahun Angkatan')
                    ->options(function () {
                        return Semester::query()
                            ->distinct()
                            ->orderByDesc('id_tahun_ajaran')
                            ->pluck('id_tahun_ajaran', 'id_tahun_ajaran')
                            ->toArray();
                    })
                    ->multiple()
                    ->searchable()
                    ->placeholder('Semua Angkatan')
                    ->query(function ($query, array $data) {
                        if (empty($data['values'])) {
                            return $query;
                        }
                        return $query->whereHas('riwayatPendidikan.periodeDaftar', function ($q) use ($data) {
                            $q->whereIn('id_tahun_ajaran', $data['values']);
                        });
                    }),

                SelectFilter::make('prodi')
                    ->label('Program Studi')
                    ->options(function () {
                        return Prodi::query()
                            ->orderBy('nama_program_studi')
                            ->pluck('nama_program_studi', 'id_prodi')
                            ->toArray();
                    })
                    ->multiple()
                    ->searchable()
                    ->placeholder('Semua Prodi')
                    ->query(function ($query, array $data) {
                        if (empty($data['values'])) {
                            return $query;
                        }
                        return $query->whereHas('riwayatPendidikan', function ($q) use ($data) {
                            $q->whereIn('id_prodi', $data['values']);
                        });
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}


<?php

namespace App\Filament\Resources\Finance\FeeStructures\Tables;

use App\Models\Finance\FeeStructure;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FeeStructuresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('angkatan')
                    ->label('Tahun Ajaran')
                    ->sortable(),
                TextColumn::make('prodi.nama_program_studi')
                    ->label('Program Studi')
                    ->searchable(),
                TextColumn::make('waktu_kuliah_enum')
                    ->label('Waktu Kuliah')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'Pagi' => 'Pagi',
                        'Sore' => 'Sore',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Pagi' => 'info',
                        'Sore' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('component.name')
                    ->label('Komponen Biaya'),
                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('angkatan')
                    ->label('Tahun Ajaran')
                    ->options(function () {
                        return FeeStructure::query()
                            ->distinct()
                            ->orderByDesc('angkatan')
                            ->pluck('angkatan', 'angkatan')
                            ->toArray();
                    })
                    ->placeholder('Semua Tahun Ajaran')
                    ->searchable(),
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

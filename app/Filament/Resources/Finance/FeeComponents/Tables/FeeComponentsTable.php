<?php

namespace App\Filament\Resources\Finance\FeeComponents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeeComponentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Komponen')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Jenis Biaya')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'RECURRING' => 'Berulang',
                        'ONE_TIME' => 'Sekali Bayar',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'RECURRING' => 'info',
                        'ONE_TIME' => 'success',
                        default => 'gray',
                    }),
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
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

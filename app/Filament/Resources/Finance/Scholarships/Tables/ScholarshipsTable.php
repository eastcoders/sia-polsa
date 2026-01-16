<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finance\Scholarships\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class ScholarshipsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Nama Beasiswa')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('funding_source')
                    ->label('Sumber Dana')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'GOVERNMENT' => 'success',
                        'FOUNDATION' => 'info',
                        'INSTITUTION' => 'warning',
                        'CORPORATE' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'GOVERNMENT' => 'Pemerintah',
                        'FOUNDATION' => 'Yayasan',
                        'INSTITUTION' => 'Institusi',
                        'CORPORATE' => 'Perusahaan',
                        default => $state,
                    }),

                TextColumn::make('coverage_percentage')
                    ->label('Coverage')
                    ->suffix('%')
                    ->alignCenter()
                    ->badge()
                    ->color(fn($state): string => $state >= 100 ? 'success' : 'warning'),

                TextColumn::make('student_scholarships_count')
                    ->label('Penerima')
                    ->counts('studentScholarships')
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('funding_source')
                    ->label('Sumber Dana')
                    ->options([
                        'GOVERNMENT' => 'Pemerintah',
                        'FOUNDATION' => 'Yayasan',
                        'INSTITUTION' => 'Institusi',
                        'CORPORATE' => 'Perusahaan',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\Finance\StudentScholarships\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use App\Enums\Finance\ScholarshipStatus;
use Filament\Tables\Filters\SelectFilter;

class StudentScholarshipsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('riwayatPendidikan.nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('riwayatPendidikan.mahasiswa.nama_lengkap')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('scholarship.name')
                    ->label('Beasiswa')
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('scholarship.coverage_percentage')
                    ->label('Coverage')
                    ->suffix('%')
                    ->alignCenter(),

                TextColumn::make('valid_from')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('valid_until')
                    ->label('Berakhir')
                    ->date('d M Y')
                    ->placeholder('Sampai Lulus')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => $state instanceof ScholarshipStatus ? $state->color() : 'gray')
                    ->formatStateUsing(fn($state) => $state instanceof ScholarshipStatus ? $state->label() : $state),

                TextColumn::make('coverage_type')
                    ->label('Tipe')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'FULL_TUITION' => 'SPP Penuh',
                        'PARTIAL_TUITION' => 'SPP Sebagian',
                        'TUITION_AND_LIVING' => 'SPP + Living',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('scholarship_id')
                    ->label('Beasiswa')
                    ->relationship('scholarship', 'name'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(
                        collect(ScholarshipStatus::cases())
                            ->mapWithKeys(fn($status) => [$status->value => $status->label()])
                    ),
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

<?php

namespace App\Filament\Dosen\Resources\FormTemplates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class FormTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->label('Judul'),
                \Filament\Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->colors(['primary' => 'UTS_LAYANAN', 'info' => 'UAS_DOSEN']),
                \Filament\Tables\Columns\TextColumn::make('evaluation_target')
                    ->label('Target'),
                \Filament\Tables\Columns\TextColumn::make('semester.nama_semester')
                    ->label('Semester')
                    ->sortable(),
                \Filament\Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('semester_id')
                    ->relationship('semester', 'nama_semester'),
                \Filament\Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'UTS_LAYANAN' => 'UTS',
                        'UAS_DOSEN' => 'UAS'
                    ]),
            ])
            ->recordActions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\Action::make('generate_tickets')
                    ->label('Bagikan Tiket')
                    ->icon('heroicon-o-ticket')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Bagikan Tiket Kuesioner')
                    ->modalDescription('Sistem akan memproses pembagian tiket kuesioner ke seluruh mahasiswa yang memenuhi syarat. Proses ini berjalan di latar belakang.')
                    ->action(function (\App\Models\FormTemplate $record) {
                        \App\Jobs\GenerateSurveyTicketsJob::dispatch($record);
                        \Filament\Notifications\Notification::make()
                            ->title('Job Distribusi Tiket Dimulai')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\BiodataMahasiswas\Tables;

use App\Models\Prodi;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\RecordActionsPosition;

class BiodataMahasiswasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sync_status')
                    ->label('Status Sync')
                    ->badge()
                    ->colors([
                        'success' => 'synced',
                        'warning' => ['pending', 'changed'],
                        'danger' => 'failed',
                        'gray' => 'server_deleted',
                    ])
                    ->tooltip(fn($record) => $record->sync_message),
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

                TextColumn::make('sync_at')
                    ->label('Sync Terakhir')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                        if (!empty($data['values'])) {
                            $query->whereHas('riwayatPendidikan', function (Builder $q) use ($data) {
                                $q->whereIn('id_prodi', $data['values']);
                            });

                        }

                        return $query;
                    })
                    ->multiple(),
                SelectFilter::make('angkatan')
                    ->label('Angkatan (Periode Masuk)')
                    ->options(function () {
                        return \App\Models\Semester::orderBy('id_semester', 'desc')
                            ->pluck('nama_semester', 'id_semester')
                            ->toArray();
                    })
                    ->default(fn() => session('active_semester_id') ?? \App\Models\Semester::where('a_periode_aktif', '1')->value('id_semester'))
                    ->preload()
                    ->searchable()
                    ->query(function (Builder $query, array $data) {
                        // dd($data['value']);
                        if ($data['value'] ?? null) {
                            $query->whereHas('riwayatPendidikan.periodeDaftar', function ($q) use ($data) {
                                $q->where('id_semester', $data['value']);
                            });
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit Data'),
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('Detail Data'),
                Action::make('push_to_feeder')
                    ->iconButton()
                    ->tooltip('Push Data')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['sync_status' => 'changed']);
                        \App\Jobs\PushBiodataMahasiswaJob::dispatch($record);
                        \Filament\Notifications\Notification::make()
                            ->title('Push dijadwalkan')
                            ->success()
                            ->send();
                    }),
            ], position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(fn($record) => $record?->riwayatPendidikan()?->delete()),
                    BulkAction::make('push_selected')
                        ->label('Push Selected to Server')
                        ->icon('heroicon-o-cloud-arrow-up')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['sync_status' => 'changed']);
                                \App\Jobs\PushBiodataMahasiswaJob::dispatch($record);
                            });
                            \Filament\Notifications\Notification::make()
                                ->title('Bulk Push dijadwalkan')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Kurikulums;

use UnitEnum;
use App\Models\Prodi;
use App\Models\Kurikulum;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\View;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Filament\Resources\Kurikulums\Pages\ManageKurikulums;

class KurikulumResource extends Resource
{
    protected static ?string $model = Kurikulum::class;

    protected static string|UnitEnum|null $navigationGroup = 'Perkulihan';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_kurikulum')
                    ->required(),
                Select::make('id_prodi')
                    ->label('Program Studi')
                    ->relationship(
                        name: 'prodi',
                        titleAttribute: 'nama_program_studi', // kolom nyata
                        modifyQueryUsing: fn(Builder $query) => $query
                            ->orderBy('nama_jenjang_pendidikan')
                            ->orderBy('nama_program_studi')
                    )
                    ->getOptionLabelFromRecordUsing(fn(Prodi $record) => $record->programStudiLengkap)
                    ->native(false)
                    ->required(),
                Select::make('id_semester')
                    ->label('Mulai Berlaku')
                    ->native(false)
                    ->relationship(
                        name: 'semester',
                        titleAttribute: 'nama_semester',
                        modifyQueryUsing: fn(Builder $query) => $query
                            ->where('id_tahun_ajaran', '>=', now()->year)
                            ->orderBy('id_tahun_ajaran', 'asc')
                            ->orderBy('nama_semester', 'asc')
                    )
                    ->required(),
                TextInput::make('jumlah_sks_wajib')
                    ->label('SKS Wajib')
                    ->required()
                    ->default(0)
                    ->numeric()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Set $set, Get $get) => $set(
                        'jumlah_sks_lulus',
                        ($get('jumlah_sks_pilihan') ?? 0) +
                        ($get('jumlah_sks_wajib') ?? 0)
                    )),
                TextInput::make('jumlah_sks_pilihan')
                    ->label('SKS Pilihan')
                    ->required()
                    ->default(0)
                    ->numeric()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Set $set, Get $get) => $set(
                        'jumlah_sks_lulus',
                        ($get('jumlah_sks_pilihan') ?? 0) +
                        ($get('jumlah_sks_wajib') ?? 0)
                    )),
                TextInput::make('jumlah_sks_lulus')
                    ->label('Jumlah SKS')
                    ->required()
                    ->default(0)
                    ->numeric()
                    ->helperText('( sks Wajib + sks Pilihan )')
                    ->readOnly(),
                View::make('livewire.table-matkul-kurikulum')
                    ->visibleOn('edit')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->rowIndex()
                    ->label('#'),
                TextColumn::make('nama_kurikulum')
                    ->searchable(),
                TextColumn::make('prodi.nama_program_studi')
                    ->searchable(),
                TextColumn::make('semester.nama_semester')
                    ->label('Mulai Berlaku')
                    ->searchable(),
                TextColumn::make('jumlah_sks_lulus')
                    ->label('SKS Lulus')
                    ->searchable(),
                TextColumn::make('jumlah_sks_wajib')
                    ->label('SKS Wajib')
                    ->searchable(),
                TextColumn::make('jumlah_sks_pilihan')
                    ->label('SKS Pilihan')
                    ->searchable(),
                TextColumn::make('sync_status')
                    ->label('Status Sync')
                    ->badge()
                    ->colors([
                        'success' => 'synced',
                        'warning' => ['pending', 'changed'],
                        'danger' => 'failed',
                    ])
                    ->tooltip(fn($record) => $record->sync_message)
                    ->sortable(),
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
                            $query->whereIn('id_prodi', $data['values']);
                        }

                        return $query;
                    })
                    ->multiple(),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn($record) => KurikulumResource::getUrl('edit', ['record' => $record->getKey()])),
                DeleteAction::make(),
                Action::make('push_to_feeder')
                    ->label('Push to Server')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['sync_status' => 'changed']);
                        \App\Jobs\PushKurikulumJob::dispatch($record);
                        \Filament\Notifications\Notification::make()
                            ->title('Push dijadwalkan')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('push_selected')
                        ->label('Push Selected to Server')
                        ->icon('heroicon-o-cloud-arrow-up')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['sync_status' => 'changed']);
                                \App\Jobs\PushKurikulumJob::dispatch($record);
                            });
                            \Filament\Notifications\Notification::make()
                                ->title('Bulk Push dijadwalkan')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageKurikulums::route('/'),
            'edit' => Pages\EditKurikulum::route('/{record}/edit'),
        ];
    }
}

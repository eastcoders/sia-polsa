<?php

namespace App\Filament\Resources\MataKuliahs;

use App\Filament\Resources\MataKuliahs\Pages\ManageMataKuliahs;
use App\Models\MataKuliah;
use App\Models\Prodi;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MataKuliahResource extends Resource
{
    protected static ?string $model = MataKuliah::class;

    protected static ?string $recordTitleAttribute = 'MataKuliah';

    protected static string|UnitEnum|null $navigationGroup = 'Perkuliahan';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_mata_kuliah')
                    ->required(),
                TextInput::make('kode_mata_kuliah')
                    ->required(),
                Select::make('id_prodi')
                    ->label('Program Studi Pengampu')
                    ->options(fn () => Prodi::orderBy('id')->pluck('nama_program_studi', 'id_prodi'))
                    ->required(),
                Select::make('id_jenis_mata_kuliah')
                    ->label('Jenis Mata Kuliah')
                    ->options([
                        'A' => 'Wajib',
                        'B' => 'Pilihan',
                        'C' => 'Wajib Peminatan',
                        'D' => 'Pilihan Peminatan',
                        'S' => 'Tugas Akhir/Skripsi/Disertasi',
                    ])
                    ->required(),
                Select::make('id_kelompok_mata_kuliah')
                    ->label('Kelompok Mata Kuliah')
                    ->options([
                        'A' => 'MPK (Mata Kuliah Pengembangan Kepribadian)',
                        'B' => 'MKK (Mata Kulia Keilmuan dan Keterampilan)',
                        'C' => 'MKB (Mata Kuliah Keahlian Berkarya)',
                        'D' => 'MPB (Mata Kuliah Perilaku Berkarya)',
                        'E' => 'MBB (Mata Kuliah Berkehidupan Bermasyarakat)',
                        'F' => 'MKU/MKDU',
                        'G' => 'MKDK (Mata Kuliah Dasar Keahlian)',
                        'H' => 'MKK',
                    ])
                    ->required(),
                TextInput::make('sks_mata_kuliah')
                    ->label('SKS Mata Kuliah')
                    ->readOnly()
                    ->required(),
                TextInput::make('sks_tatap_muka')
                    ->label('SKS Tatap Muka')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => $set(
                        'sks_mata_kuliah',
                        ($get('sks_tatap_muka') ?? 0) +
                        ($get('sks_praktek') ?? 0) +
                        ($get('sks_praktek_lapangan') ?? 0) +
                        ($get('sks_simulasi') ?? 0)
                    )),

                TextInput::make('sks_praktek')
                    ->label('SKS Praktik')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => $set(
                        'sks_mata_kuliah',
                        ($get('sks_tatap_muka') ?? 0) +
                        ($get('sks_praktek') ?? 0) +
                        ($get('sks_praktek_lapangan') ?? 0) +
                        ($get('sks_simulasi') ?? 0)
                    )),

                TextInput::make('sks_praktek_lapangan')
                    ->label('SKS Praktik Lapangan')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => $set(
                        'sks_mata_kuliah',
                        ($get('sks_tatap_muka') ?? 0) +
                        ($get('sks_praktek') ?? 0) +
                        ($get('sks_praktek_lapangan') ?? 0) +
                        ($get('sks_simulasi') ?? 0)
                    )),

                TextInput::make('sks_simulasi')
                    ->label('SKS Simulasi')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => $set(
                        'sks_mata_kuliah',
                        ($get('sks_tatap_muka') ?? 0) +
                        ($get('sks_praktek') ?? 0) +
                        ($get('sks_praktek_lapangan') ?? 0) +
                        ($get('sks_simulasi') ?? 0)
                    )),
                DatePicker::make('tanggal_mulai_efektif')
                    ->label('Tanggal Mulai Efektif'),
                DatePicker::make('tanggal_akhir_efektif')
                    ->label('Tanggal Akhir Efektif')
                    ->afterOrEqual('tanggal_mulai_efektif')
                    ->validationMessages([
                        'after_or_equal' => 'Tanggal akhir efektif tidak boleh lebih awal dari tanggal mulai efektif.',
                    ]),
                TextInput::make('metode_kuliah')
                    ->label('Metode Kuliah')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('MataKuliah')
            ->columns([
                TextColumn::make('sync_status')
                    ->label('Status Sync')
                    ->badge()
                    ->colors([
                        'success' => 'synced',
                        'warning' => ['pending', 'changed'],
                        'danger' => 'failed',
                    ])
                    ->tooltip(fn ($record) => $record->sync_message),
                TextColumn::make('id')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('kode_mata_kuliah')
                    ->searchable()
                    ->color('info'),
                TextColumn::make('nama_mata_kuliah')
                    ->searchable(),
                TextColumn::make('prodi.nama_program_studi')
                    ->searchable(),

                TextColumn::make('sks_mata_kuliah')
                    ->label('Bobot MK (SKS)')
                    ->searchable(),
                TextColumn::make('id_jenis_mata_kuliah')
                    ->label('Jenis Mata Kuliah')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'A' => 'Wajib',
                        'B' => 'Pilihan',
                        'C' => 'Wajib Peminatan',
                        'D' => 'Pilihan Peminatan',
                        'S' => 'Tugas Akhir/Skripsi/Disertasi',
                        default => $state, // atau 'Tidak Diketahui'
                    }),

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
                        if (! empty($data['values'])) {
                            $query->whereIn('id_prodi', $data['values']);
                        }

                        return $query;
                    })
                    ->multiple(),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit Data')
                    ->mutateDataUsing(function (array $data): array {
                        $data['sync_status'] = 'changed';

                        return $data;
                    })
                    ->closeModalByClickingAway(false),
                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Delete Data'),
                Action::make('push_to_feeder')
                    ->label('Push to Server')
                    ->iconButton()
                    ->tooltip('Push Data')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        if ($record->sync_status == 'changed') {
                            \App\Jobs\PushMataKuliahJob::dispatch($record);
                            \Filament\Notifications\Notification::make()
                                ->title('Push dijadwalkan')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Tidak ada yang perlu di push')
                                ->danger()
                                ->send();
                        }
                    }),
            ], RecordActionsPosition::BeforeColumns)
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
                                \App\Jobs\PushMataKuliahJob::dispatch($record);
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
            'index' => ManageMataKuliahs::route('/'),
        ];
    }
}

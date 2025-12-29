<?php

namespace App\Filament\Resources\KelasKuliahs;

use App\Filament\Resources\KelasKuliahs\Pages\ManageKelasKuliahs;
use App\Livewire\Perkuliahan\AktivitasMengajar;
use App\Livewire\Perkuliahan\PesertaKelasTable;
use App\Models\KelasKuliah;
use App\Models\Prodi;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Livewire as LivewireSchema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class KelasKuliahResource extends Resource
{
    protected static ?string $model = KelasKuliah::class;

    protected static string|UnitEnum|null $navigationGroup = 'Perkuliahan';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Kelas Perkuliahan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kelas Kuliah')
                    ->columnSpanFull()
                    ->columns(2)
                    ->description('Prevent abuse by limiting the number of requests per period')
                    ->schema([
                        // ...
                        Select::make('id_prodi')
                            ->label('Program Studi')
                            ->relationship(
                                name: 'prodi',
                                titleAttribute: 'nama_program_studi',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->orderBy('nama_jenjang_pendidikan')
                                    ->orderBy('nama_program_studi')
                            )
                            ->getOptionLabelFromRecordUsing(fn (Prodi $record) => $record->programStudiLengkap)
                            ->native(false)
                            ->required(),
                        Select::make('id_semester')
                            ->label('Semester')
                            ->native(false)
                            ->relationship(
                                name: 'semester',
                                titleAttribute: 'nama_semester',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->where('a_periode_aktif', '1')
                                    ->where('id_tahun_ajaran', '>=', now()->year)
                                    ->orderBy('id_tahun_ajaran', 'asc')
                                    ->orderBy('nama_semester', 'asc')
                            )
                            ->required(),
                        TextInput::make('nama_kelas_kuliah')
                            ->label('Nama Kelas Kuliah')
                            ->maxLength(5)
                            ->required()
                            ->helperText('Masksimal 5 Huruf'),
                        Select::make('id_matkul')
                            ->relationship('matkul', 'nama_mata_kuliah')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('lingkup')
                            ->native(false)
                            ->options(['1' => 'Internal', '2' => 'External', '3' => 'Campuran']),
                        Select::make('mode')
                            ->native(false)
                            ->options(['O' => 'Online', 'F' => 'Offline', 'M' => 'Campuran']),
                        DatePicker::make('tanggal_mulai_efektif'),
                        DatePicker::make('tanggal_akhir_efektif'),
                        Tabs::make('Tabs')
                            ->visibleOn(['edit', 'view'])
                            ->columnSpanFull()
                            ->tabs([
                                Tab::make('Dosen Pengajar')
                                    ->schema([
                                        LivewireSchema::make(AktivitasMengajar::class),
                                    ]),
                                Tab::make('Mahasiswa KRS/Peserta Kelas')
                                    ->schema([
                                        LivewireSchema::make(PesertaKelasTable::class),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
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
                    ])
                    ->tooltip(fn ($record) => $record->sync_message),
                TextColumn::make('id')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('prodi.programStudiLengkap')
                    ->label('Program Studi')
                    ->searchable(),
                TextColumn::make('semester.nama_semester')
                    ->searchable(),
                TextColumn::make('matkul.kode_mata_kuliah')
                    ->label('Kode MK')
                    ->color('info')
                    ->searchable(),
                TextColumn::make('matkul.nama_mata_kuliah')
                    ->label('Nama Mata Kuliah')
                    ->searchable(),
                TextColumn::make('nama_kelas_kuliah')
                    ->label('Nama Kelas')
                    ->searchable(),
                TextColumn::make('sks_mk')
                    ->label('Bobot MK (SKS)')
                    ->searchable(),
                TextColumn::make('sync_at')
                    ->label('Sync Terakhir')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                \Filament\Tables\Filters\SelectFilter::make('semester')
                    ->relationship('semester', 'nama_semester')
                    ->label('Semester')
                    ->default(fn () => session('active_semester_id') ?? \App\Models\Semester::where('a_periode_aktif', 1)->value('id_semester'))
                    ->preload()
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton(),
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit Data')
                    ->url(fn ($record) => KelasKuliahResource::getUrl('add-dosen-pengajar', ['record' => $record->getKey()])),
                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Delete Data'),
                Action::make('push_to_feeder')
                    ->iconButton()
                    ->tooltip('Push Data')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['sync_status' => 'changed']);
                        \App\Jobs\PushKelasKuliahJob::dispatch($record);
                        \Filament\Notifications\Notification::make()
                            ->title('Push dijadwalkan')
                            ->success()
                            ->send();
                    }),
            ], position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    BulkAction::make('push_selected')
                        ->label('Push Selected to Server')
                        ->icon('heroicon-o-cloud-arrow-up')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['sync_status' => 'changed']);
                                \App\Jobs\PushKelasKuliahJob::dispatch($record);
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
            'index' => ManageKelasKuliahs::route('/'),
            'add-dosen-pengajar' => Pages\AddDosenPengajarPage::route('{record}/add-dosen-pengajar'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

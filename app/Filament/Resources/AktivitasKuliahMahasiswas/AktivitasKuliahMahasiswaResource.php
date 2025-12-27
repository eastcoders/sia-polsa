<?php

namespace App\Filament\Resources\AktivitasKuliahMahasiswas;

use BackedEnum;
use Filament\Forms;
use Filament\Tables;
use App\Models\Semester;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Jobs\CalculateAkmJob;
use Filament\Actions\BulkAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use App\Models\AktivitasKuliahMahasiswa;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Collection;
use App\Jobs\PushAktivitasKuliahMahasiswaJob;
use Filament\Schemas\Components\Utilities\Set;
use App\Filament\Resources\AktivitasKuliahMahasiswas\Pages\ManageAktivitasKuliahMahasiswas;

class AktivitasKuliahMahasiswaResource extends Resource
{
    protected static ?string $model = AktivitasKuliahMahasiswa::class;

    protected static ?string $pluralModelLabel = 'Aktivitas Kuliah';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('id_registrasi_mahasiswa')
                    ->label('Mahasiswa')
                    ->required()
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search) {
                        return \App\Models\RiwayatPendidikan::query()
                            ->whereHas('mahasiswa', function ($query) use ($search) {
                                $query->where('nama_lengkap', 'like', "%{$search}%");
                            })
                            ->orWhere('nim', 'like', "%{$search}%")
                            ->orWhere('id_registrasi_mahasiswa', 'like', "%{$search}%")
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(function ($item) {
                                return [$item->id_registrasi_mahasiswa => "{$item->mahasiswa->nama_lengkap} - {$item->nim} - {$item->prodi->nama_program_studi}"];
                            });
                    })
                    ->getOptionLabelUsing(function ($value) {
                        $item = \App\Models\RiwayatPendidikan::with('mahasiswa')->find($value);
                        return $item ? "{$item->nim} - {$item->mahasiswa->nama_lengkap}" : null;
                    })
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, $component) {
                        if ($state) {
                            $riwayat = \App\Models\RiwayatPendidikan::find($state);
                            if ($riwayat && empty($riwayat->id_server)) {
                                Notification::make()
                                    ->warning()
                                    ->title('Peringatan Sync')
                                    ->body('Data Riwayat Pendidikan ini belum disync ke server (id_server kosong). Pastikan data mahasiswa dipush terlebih dahulu.')
                                    ->persistent()
                                    ->send();
                            }
                        }
                    }),
                Forms\Components\Select::make('id_semester')
                    ->label('Semester')
                    ->required()
                    ->native(false)
                    ->options(Semester::where('a_periode_aktif', '1')->orderBy('id_semester', 'desc')->pluck('nama_semester', 'id_semester'))
                    ->default(fn() => session('active_semester_id') ?? Semester::where('a_periode_aktif', '1')->value('id_semester')),
                Forms\Components\Select::make('id_status_mahasiswa')
                    ->label('Status Mahasiswa')
                    ->required()
                    ->relationship('statusMahasiswa', 'nama_status_mahasiswa')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('ips')
                    ->label('IPS (Indeks Prestasi Semester)')
                    ->numeric()
                    ->maxValue(4.00),
                Forms\Components\TextInput::make('ipk')
                    ->label('IPK (Indeks Prestasi Komulatif)')
                    ->numeric()
                    ->maxValue(4.00),
                Forms\Components\TextInput::make('sks_semester')
                    ->label('Jumlah SKS Semester')
                    ->numeric(),
                Forms\Components\TextInput::make('sks_total')
                    ->label('Jumlah SKS Total')
                    ->numeric(),
                Forms\Components\TextInput::make('biaya_kuliah_smt')
                    ->label('Biaya Kuliah Semester')
                    ->prefix('Rp'),
                Forms\Components\Select::make('id_pembiayaan')
                    ->label('Pembiayaan')
                    ->relationship('pembiayaan', 'nama_pembiayaan')
                    ->searchable()
                    ->columnSpanFull()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('riwayatPendidikan.nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('riwayatPendidikan.mahasiswa.nama_lengkap')
                    ->label('Nama Mahasiswa')
                    ->searchable(),
                TextColumn::make('semester.nama_semester')
                    ->label('Semester')
                    ->sortable(),
                TextColumn::make('ips')
                    ->label('IPS'),
                TextColumn::make('ipk')
                    ->label('IPK'),
                TextColumn::make('sks_semester')
                    ->label('SKS Smt'),
                TextColumn::make('sks_total')
                    ->label('SKS Total'),
                TextColumn::make('id_status_mahasiswa')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => 'A',
                        'warning' => 'C',
                        'danger' => ['N', 'D', 'K'],
                    ]),
                TextColumn::make('id_server')
                    ->label('Sync Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? 'Synced' : 'Pending')
                    ->colors([
                        'success' => fn($state) => $state !== null,
                        'warning' => fn($state) => $state === null,
                    ]),
            ])
            ->filters([
                SelectFilter::make('id_semester')
                    ->label('Semester')
                    ->options(fn() => Semester::orderBy('id_semester', 'desc')->take(10)->pluck('nama_semester', 'id_semester')->toArray()),
                SelectFilter::make('id_status_mahasiswa')
                    ->label('Status')
                    ->options([
                        'A' => 'Aktif',
                        'C' => 'Cuti',
                        'N' => 'Non-Aktif',
                        'L' => 'Lulus',
                    ]),
            ])
            ->actions([
                Action::make('sync')
                    ->label('Sync')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (AktivitasKuliahMahasiswa $record) {
                        try {
                            PushAktivitasKuliahMahasiswaJob::dispatch($record);
                            Notification::make()->title('Sync Dispatched')->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('calculate')
                        ->label('Hitung Ulang (Re-Calculate)')
                        ->icon('heroicon-o-calculator')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                CalculateAkmJob::dispatch($record->id_registrasi_mahasiswa);
                            }
                            Notification::make()->title('Calculation Dispatched')->success()->send();
                        }),
                    BulkAction::make('sync_bulk')
                        ->label('Sync Terpilih')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                PushAktivitasKuliahMahasiswaJob::dispatch($record);
                            }
                            Notification::make()->title('Bulk Sync Dispatched')->success()->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAktivitasKuliahMahasiswas::route('/'),
        ];
    }
}

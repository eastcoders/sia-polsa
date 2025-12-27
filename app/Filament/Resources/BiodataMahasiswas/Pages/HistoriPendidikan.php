<?php

namespace App\Filament\Resources\BiodataMahasiswas\Pages;

use BackedEnum;
use App\Models\Prodi;
use App\Models\AllProdi;
use App\Models\Semester;
use App\Models\ProfilePT;
use App\Models\JalurMasuk;
use App\Models\Pembiayaan;
use Filament\Tables\Table;
use App\Models\BidangMinat;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use App\Models\PerguruanTinggi;
use App\Models\JenisPendaftaran;
use App\Models\RiwayatPendidikan;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use App\Filament\Resources\BiodataMahasiswas\BiodataMahasiswaResource;

class HistoriPendidikan extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public static function getNavigationLabel(): string
    {
        return 'Histori Pendidikan';
    }

    protected static string $resource = BiodataMahasiswaResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Histori Pendidikan - ' . $this->record->nama_lengkap;
    }

    protected string $view = 'filament.resources.biodata-mahasiswas.pages.histori-pendidikan';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RiwayatPendidikan::query()
                    ->where('id_mahasiswa', $this->record->id_mahasiswa) // sesuaikan FK-nya
            )
            ->heading('Riwayat Pendidikan Mahasiswa')
            ->description('Data Riwayat Pendidikan Mahasiswa')
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
                TextColumn::make('nim')
                    ->label('NIM'),

                TextColumn::make('prodi.nama_program_studi')
                    ->label('Program Studi'),

                TextColumn::make('jenisPendaftaran.nama_jenis_daftar')
                    ->label('Jenis Pendaftaran'),

                TextColumn::make('periodeDaftar.nama_semester')
                    ->label('Periode Masuk'),

                TextColumn::make('tanggal_daftar')
                    ->label('Tanggal Masuk'),

            ])
            ->headerActions([
                $this->getFormRiwayatPendidikan(),
            ])
            ->recordActions([
                Action::make('view')
                    ->modalHeading('Detail Riwayat Pendidikan')
                    ->schema($this->getRiwayatPendidikanFormSchema())
                    ->tooltip('Detail')
                    ->fillForm(fn(RiwayatPendidikan $record): array => $record->toArray())
                    ->modalWidth('4xl')
                    ->disabledSchema()
                    ->iconButton()
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->modalSubmitAction(false)
                    ->closeModalByClickingAway(false),
                Action::make('edit')
                    ->modalHeading('Detail Riwayat Pendidikan')
                    ->schema($this->getRiwayatPendidikanFormSchema())
                    ->fillForm(fn(RiwayatPendidikan $record): array => $record->toArray())
                    ->mutateDataUsing(fn(array $data): array => $data)
                    ->modalWidth('4xl')
                    ->tooltip('Edit Data')
                    ->iconButton()
                    ->icon('heroicon-m-pencil-square')
                    ->color('warning')
                    ->action(function (array $data, RiwayatPendidikan $record) {
                        DB::beginTransaction();
                        try {

                            $data['sync_status'] = 'changed';

                            DB::commit();

                            $record->update($data);
                            $record->mahasiswa->update([
                                'sync_status' => 'changed',
                            ]);

                            Notification::make()
                                ->title('Berhasil Mengupdate Riwayat Pendidikan')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();

                            Notification::make()
                                ->title('Gagal Mengupdate Riwayat Pendidikan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->closeModalByClickingAway(false),
                Action::make('delete')
                    ->requiresConfirmation()
                    ->iconButton()
                    ->tooltip('Hapus')
                    ->color('danger')
                    ->disabled(fn(RiwayatPendidikan $record): bool => $record->id_server != null)
                    ->icon('heroicon-m-trash')
                    ->action(function (RiwayatPendidikan $record) {
                        $record->delete();
                        Notification::make()
                            ->title('Berhasil Menghapus Riwayat Pendidikan')
                            ->success()
                            ->send();
                    }),
            ], position: RecordActionsPosition::BeforeColumns);
    }

    public function getFormRiwayatPendidikan()
    {
        return Action::make('add_riwayat_pendidikan')
            ->label('Tambah')
            ->modalHeading('Histori Pendidikan')
            ->schema($this->getRiwayatPendidikanFormSchema())
            ->action(function (array $data) {
                DB::beginTransaction();
                try {
                    $data['id_mahasiswa'] = $this->record->id_mahasiswa;
                    $data['id_biodata_mahasiswa'] = $this->record->id;
                    $data['id_perguruan_tinggi'] = ProfilePT::first()->id_perguruan_tinggi ?? '0';
                    $data['id_registrasi_mahasiswa'] = (string) Str::uuid();
                    $data['sync_status'] = 'pending';

                    RiwayatPendidikan::create($data);

                    DB::commit();

                    Notification::make()
                        ->title('Berhasil Menambah Riwayat Pendidikan')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    DB::rollBack();

                    Notification::make()
                        ->title('Gagal Menambah Riwayat Pendidikan')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->closeModalByClickingAway(false);
    }

    public function getRiwayatPendidikanFormSchema()
    {
        return [
            Grid::make(2)
                ->schema([
                    TextInput::make('nim')
                        ->label('NIM')
                        ->required(),

                    Select::make('id_jenis_daftar')
                        ->required()
                        ->label('Jenis Pendaftaran')
                        ->options(fn() => JenisPendaftaran::orderBy('id_jenis_daftar')->pluck('nama_jenis_daftar', 'id_jenis_daftar'))
                        ->searchable()
                        ->live(),

                    Select::make('id_jalur_daftar')
                        ->required()
                        ->label('Jalur Pendaftaran')
                        ->options(fn() => JalurMasuk::orderBy('id_jalur_masuk')->pluck('nama_jalur_masuk', 'id_jalur_masuk'))
                        ->searchable(),

                    Select::make('id_periode_masuk')
                        ->label('Periode Masuk')
                        ->required()
                        ->options(fn() => Semester::where('a_periode_aktif', '1')
                            ->orderBy('id_semester')->pluck('nama_semester', 'id_semester'))
                        ->searchable(),

                    DatePicker::make('tanggal_daftar')
                        ->label('Tanggal Masuk')
                        ->required(),

                    Select::make('id_pembiayaan')
                        ->required()
                        ->label('Pembiayaan')
                        ->options(fn() => Pembiayaan::orderBy('id_pembiayaan')->pluck('nama_pembiayaan', 'id_pembiayaan')),

                    TextInput::make('biaya_masuk')
                        ->label('Biaya Masuk')
                        ->numeric()
                        ->required(),

                    Select::make('id_perguruan_tinggi')
                        ->label('Perguruan Tinggi')
                        ->options(ProfilePT::query()->pluck('nama_perguruan_tinggi', 'id_perguruan_tinggi'))
                        ->default(ProfilePT::query()->value('id_perguruan_tinggi'))
                        ->disabled(),

                    Select::make('id_prodi')
                        ->required()
                        ->label('Fakultas/Program Studi')
                        ->searchable()
                        ->options(fn() => Prodi::orderBy('id_prodi')->pluck('nama_program_studi', 'id_prodi')),

                    Select::make('id_bidang_minat')
                        ->label('Peminatan')
                        ->options(fn() => BidangMinat::orderBy('id_bidang_minat')->pluck('nm_bidang_minat', 'id_bidang_minat')),

                    Select::make('id_perguruan_tinggi_asal')
                        ->required(fn(Get $get) => filled($get('id_jenis_daftar')) && $get('id_jenis_daftar') != '1')
                        ->visible(fn(Get $get) => filled($get('id_jenis_daftar')) && $get('id_jenis_daftar') != '1')
                        ->label('Perguruan Tinggi Asal')
                        ->searchable()
                        ->options(fn() => PerguruanTinggi::orderBy('nama_perguruan_tinggi')->pluck('nama_perguruan_tinggi', 'id_perguruan_tinggi'))
                        ->live()
                        ->afterStateUpdated(fn(Set $set) => $set('id_prodi_asal', null)),

                    Select::make('id_prodi_asal')
                        ->required(fn(Get $get) => filled($get('id_jenis_daftar')) && $get('id_jenis_daftar') != '1')
                        ->visible(fn(Get $get) => filled($get('id_jenis_daftar')) && $get('id_jenis_daftar') != '1')
                        ->label('Fakultas/Program Studi Asal')
                        ->searchable()
                        ->options(function (Get $get) {
                            $idPt = $get('id_perguruan_tinggi_asal');

                            if (!$idPt) {
                                return [];
                            }

                            return AllProdi::query()
                                ->where('id_perguruan_tinggi', $idPt)
                                ->orderBy('nama_program_studi')
                                ->get()
                                ->mapWithKeys(function ($item) {
                                    return [
                                        $item->id_prodi => $item->nama_jenjang_pendidikan . ' - ' . $item->nama_program_studi,
                                    ];
                                });
                        })
                        ->disabled(fn(Get $get) => blank($get('id_perguruan_tinggi_asal'))),
                ]),
        ];
    }
}

<?php

namespace App\Livewire\Perkuliahan;

use App\Models\Dosen;
use App\Models\DosenPengajarKelasKuliah;
use App\Models\PenugasanDosen;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class AktivitasMengajar extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public ?Model $record = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => DosenPengajarKelasKuliah::where(
                'id_kelas_kuliah',
                $this->record->id_kelas_kuliah
            ))
            ->columns([
                TextColumn::make('id')
                    ->rowIndex()
                    ->label('No.'),
                TextColumn::make('registrasiDosen.dosen.nama_dosen')
                    ->description(
                        fn(DosenPengajarKelasKuliah $record): string =>
                        'Alias: ' . ($record->dosenAlias?->nama_dosen ?? '-')
                    ),
                TextColumn::make('sks_substansi_total')
                    ->label('Bobot SKS'),
                TextColumn::make('rencana_minggu_pertemuan')
                    ->label('Rencana Pertemuan'),
                TextColumn::make('realisasi_minggu_pertemuan')
                    ->label('Realisasi Pertemuan'),
                TextColumn::make('id_jenis_evaluasi')
                    ->label('Jenis Evaluasi')
                    ->formatStateUsing(fn($state) => match ($state) {
                        '1' => 'Evaluasi Akademik',
                        '2' => 'Aktivitas Partisipatif',
                        '3' => 'Hasil Proyek',
                        '4' => 'Kognitif/Pengetahuan',
                        default => $state,
                    }),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make('addDosen')
                    ->label('Tambah Dosen Pengajar')
                    ->schema([
                        Section::make()
                            ->columns(2)
                            ->schema([
                                Select::make('id_registrasi_dosen')
                                    ->label('Dosen')
                                    ->required()
                                    ->columnSpanFull()
                                    ->options(function () {
                                        return PenugasanDosen::join('dosens', 'penugasan_dosens.id_dosen', '=', 'dosens.id_dosen')
                                            ->join('prodis', 'penugasan_dosens.id_prodi', '=', 'prodis.id_prodi')
                                            ->where('id_tahun_ajaran', now()->year)
                                            ->select(
                                                DB::raw('ROW_NUMBER() OVER (ORDER BY dosens.nama_dosen) as nomor_urut'),
                                                'penugasan_dosens.id_registrasi_dosen',
                                                DB::raw("CONCAT(
                                                                dosens.nama_dosen,
                                                                ' - (',
                                                                prodis.nama_jenjang_pendidikan,
                                                                ' ',
                                                                prodis.nama_program_studi,
                                                                ')'
                                                            ) as display_name")
                                            )
                                            ->get()
                                            ->mapWithKeys(function ($row) {
                                                return [
                                                    $row->id_registrasi_dosen => $row->nomor_urut . '. ' . $row->display_name
                                                ];
                                            })
                                            ->toArray();

                                    })
                                    ->searchable(),
                                TextInput::make('sks_substansi_total')
                                    ->label('Bobot SKS ')
                                    ->required()
                                    ->numeric(),
                                TextInput::make('rencana_minggu_pertemuan')
                                    ->required()
                                    ->numeric(),
                                TextInput::make('realisasi_minggu_pertemuan')
                                    ->numeric()
                                    ->default(0),
                                Select::make('id_jenis_evaluasi')
                                    ->label('Jensi Evaluasi')
                                    ->required()
                                    ->options([
                                        '1' => 'Evaluasi Akademik',
                                        '2' => 'Aktivitas Partisipatif',
                                        '3' => 'Hasil Proyek',
                                        '4' => 'Kognitif/Pengetahuan',
                                    ]),
                                Checkbox::make('punya_alias')
                                    ->label('Sebagai dosen alias?')
                                    ->helperText('Centeng bila dosen pengampu kelas belum terdaftar di PDDIKTI.')
                                    ->live(),
                                Select::make('id_dosen_alias')
                                    ->disabled(fn(Get $get) => $get('punya_alias'))
                                    ->label('Dosen Alias')
                                    ->required(fn(Get $get) => $get('punya_alias'))
                                    ->options(
                                        fn() => Dosen::where('sync_at', null)
                                            ->pluck('nama_dosen', 'id_dosen')
                                            ->toArray()
                                    )
                                    ->searchable(),
                            ]),
                    ])
                    ->mutateDataUsing(function (array $data) {
                        $data['id_aktivitas_mengajar'] = Str::uuid()->toString();
                        $data['id_kelas_kuliah'] = $this->record->id_kelas_kuliah;
                        $data['punya_alias'] = $data['punya_alias'] == true ? '1' : '0';

                        return $data;
                    })
                    ->createAnother(false)
                    ->closeModalByClickingAway(false)
                    ->modalSubmitActionLabel('Simpan'),
            ])
            ->recordActions([
                EditAction::make('editDosenPengajar')
                    ->label('Edit')
                    ->modalHeading('Edit Dosen Pengajar')
                    ->modalSubmitActionLabel('Simpan Perubahan')
                    ->schema([
                        Section::make()
                            ->columns(2)
                            ->schema([
                                Select::make('id_registrasi_dosen')
                                    ->label('Dosen')
                                    ->required()
                                    ->disabled(true)
                                    ->columnSpanFull()
                                    ->options(function ($get, $record) {
                                        $id_kelas_kuliah = $this->record->id_kelas_kuliah;

                                        // Ambil dosen yang sudah terdaftar di kelas ini
                                        $dosenTerdaftarQuery = DosenPengajarKelasKuliah::where('id_kelas_kuliah', $id_kelas_kuliah);

                                        // Jika sedang mengedit, jangan sertakan record yang sedang diedit
                                        if ($record && $record->id) {
                                            $dosenTerdaftarQuery = $dosenTerdaftarQuery->where('id', '!=', $record->id);
                                        }

                                        $dosenTerdaftar = $dosenTerdaftarQuery->pluck('id_registrasi_dosen')->toArray();

                                        return PenugasanDosen::join('dosens', 'penugasan_dosens.id_dosen', '=', 'dosens.id_dosen')
                                            ->join('prodis', 'penugasan_dosens.id_prodi', '=', 'prodis.id_prodi')
                                            ->where('id_tahun_ajaran', now()->year)
                                            ->whereNotIn('penugasan_dosens.id_registrasi_dosen', $dosenTerdaftar)
                                            ->select(
                                                'penugasan_dosens.id_registrasi_dosen',
                                                DB::raw("CONCAT(dosens.nama_dosen, ' - (', prodis.nama_jenjang_pendidikan,' ', prodis.nama_program_studi, ')') as display_name")
                                            )
                                            ->pluck('display_name', 'id_registrasi_dosen')
                                            ->toArray();
                                    })
                                    ->searchable(),
                                TextInput::make('sks_substansi_total')
                                    ->label('Bobot SKS ')
                                    ->required()
                                    ->numeric(),
                                TextInput::make('rencana_minggu_pertemuan')
                                    ->required()
                                    ->numeric(),
                                TextInput::make('realisasi_minggu_pertemuan')
                                    ->numeric()
                                    ->default(0),
                                Select::make('id_jenis_evaluasi')
                                    ->label('Jenis Evaluasi')
                                    ->required()
                                    ->options([
                                        '1' => 'Evaluasi Akademik',
                                        '2' => 'Aktivitas Partisipatif',
                                        '3' => 'Hasil Proyek',
                                        '4' => 'Kognitif/Pengetahuan',
                                    ]),
                                Checkbox::make('punya_alias')
                                    ->label('Sebagai dosen alias?')
                                    ->helperText('Centang bila dosen pengampu kelas belum terdaftar di PDDIKTI.')
                                    ->live(),
                                Select::make('id_dosen_alias')
                                    ->disabled(fn(Get $get) => !$get('punya_alias'))
                                    ->label('Dosen Alias')
                                    ->required(fn(Get $get) => $get('punya_alias'))
                                    ->options(
                                        fn() => Dosen::where('sync_at', null)
                                            ->pluck('nama_dosen', 'id_dosen')
                                            ->toArray()
                                    )
                                    ->searchable(),
                            ]),
                    ]),

                DeleteAction::make('hapusDosenPengajar')
                    ->label('Hapus')
                    ->icon('heroicon-m-trash')
                    ->modalHeading('Hapus Dosen Pengajar')
                    ->modalDescription('Apakah kamu yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.')
                    ->requiresConfirmation(),
            ], position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.perkuliahan.aktivitas-mengajar');
    }
}

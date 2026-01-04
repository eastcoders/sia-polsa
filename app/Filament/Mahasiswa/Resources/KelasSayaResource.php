<?php

namespace App\Filament\Mahasiswa\Resources;

use App\Filament\Mahasiswa\Resources\KelasSayaResource\Pages;
use App\Models\KelasKuliah;
use App\Models\Semester;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use UnitEnum;

class KelasSayaResource extends Resource
{
    protected static ?string $model = KelasKuliah::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|UnitEnum|null $navigationGroup = 'Perkuliahan';

    protected static ?string $navigationLabel = 'Kelas Saya';

    protected static ?string $slug = 'kelas-saya';

    protected static ?int $navigationSort = 1;

    // Prevent creation/edit/delete since this is a student view
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Safety check: ensure user is logged in
        if (! $user) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        // Logic Trace:
        // 1. User -> BiodataMahasiswa (via mahasiswa_id in users table, matches id in biodata_mahasiswas)
        // 2. BiodataMahasiswa -> RiwayatPendidikan (via id_mahasiswa UUID)
        // 3. RiwayatPendidikan -> PesertaKelasKuliah (via id_registrasi_mahasiswa)
        // 4. PesertaKelasKuliah -> KelasKuliah (via id_kelas_kuliah)

        // We are querying KelasKuliah, so we look backwards:
        // KelasKuliah hasMany PesertaKelasKuliah
        // PesertaKelasKuliah belongsTo RiwayatPendidikan
        // RiwayatPendidikan belongsTo BiodataMahasiswa (conceptually, via id_mahasiswa)

        return parent::getEloquentQuery()
            ->with([
                'jadwalPerkuliahan.ruangKelas',
                'pesertaKelas' => function ($query) use ($user) {
                    $query->whereHas('riwayatPendidikan', function ($rxQuery) use ($user) {
                        if ($user->mahasiswa) {
                            $rxQuery->where('id_mahasiswa', $user->mahasiswa->id_mahasiswa);
                        }
                    })->with('riwayatPendidikan');
                },
            ])
            ->whereHas('pesertaKelas', function (Builder $query) use ($user) {
                $query->whereHas('riwayatPendidikan', function (Builder $rxQuery) use ($user) {
                    // Filter by the UUID of the student attached to the current user
                    // $user->mahasiswa is the BiodataMahasiswa model
                    // $user->mahasiswa->id_mahasiswa is the UUID
                    if ($user->mahasiswa) {
                        $rxQuery->where('id_mahasiswa', $user->mahasiswa->id_mahasiswa);
                    } else {
                        // Fallback if user not linked correctly
                        $rxQuery->whereRaw('1 = 0');
                    }
                });
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('matkul.kode_mata_kuliah')
                    ->label('Kode MK')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('matkul.nama_mata_kuliah')
                    ->label('Mata Kuliah')
                    ->description(fn (KelasKuliah $record) => $record->nama_kelas_kuliah)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jadwal_perkuliahan_summary')
                    ->label('Jadwal')
                    ->html()
                    ->state(function (KelasKuliah $record) {
                        // The pesertaKelas relation is already filtered in getEloquentQuery to only include
                        // the current user's participation. So we can just take the first one.
                        $peserta = $record->pesertaKelas->first();
                        $shift = $peserta?->riwayatPendidikan->waktu_kuliah;

                        $jadwals = $record->jadwalPerkuliahan;

                        if ($shift) {
                            $jadwals = $jadwals->filter(function ($jadwal) use ($shift) {
                                // Filter schedule based on student's shift (Pagi/Sore).
                                // If schedule has no shift specified, it applies to all.
                                return empty($jadwal->kelas_pagi_sore) ||
                                    strcasecmp($jadwal->kelas_pagi_sore, $shift) === 0;
                            });
                        }

                        if ($jadwals->isEmpty()) {
                            return null;
                        }

                        return $jadwals->map(function ($jadwal) {
                            $ruang = $jadwal->ruangKelas->nama_ruang_kelas ?? '-';
                            $hari = Str::ucfirst($jadwal->hari);
                            $jamMulai = \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i');
                            $jamSelesai = \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i');

                            return <<<HTML
                            <div class="mb-2 last:mb-0">
                                <span class="font-semibold text-primary-600 bg-primary-50 px-2 py-0.5 rounded text-xs">
                                    {$hari}
                                </span>
                                <div class="text-xs mt-1 text-gray-600">
                                    <span class="font-medium">{$jamMulai} - {$jamSelesai}</span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    Ruang: {$ruang}
                                </div>
                            </div>
HTML;
                        })->implode('');
                    })
                    ->default(function () {
                        return <<<'HTML'
                            <div class="mb-2 last:mb-0">
                                <span class="font-semibold text-danger-600 bg-danger-50 px-2 py-0.5 rounded text-xs">
                                    Jadwal Belum Diatur
                                </span>
                            </div>
HTML;
                    }),

                Tables\Columns\TextColumn::make('dosenPengajarKelasKuliah.registrasiDosen.dosen.nama_dosen')
                    ->label('Dosen Pengajar')
                    ->listWithLineBreaks()
                    ->limitList(2),

                Tables\Columns\TextColumn::make('sks_mk')
                    ->label('SKS')
                    ->alignment('center'),

            ])
            ->defaultSort('id_semester', 'desc')
            ->filters([
                SelectFilter::make('id_semester')
                    ->label('Semester')
                    ->options(Semester::query()->orderBy('id_semester', 'desc')->pluck('nama_semester', 'id_semester'))
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Action::make('riwayat_absensi')
                    ->label('Riwayat Absensi')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->button()
                    ->outlined()
                    ->modalHeading(fn (KelasKuliah $record) => 'Riwayat Absensi - '.$record->nama_kelas_kuliah)
                    ->modalContent(function (KelasKuliah $record) {
                        // Optimasi query: load pertemuan dan hanya presensi mahasiswa yang bersangkutan
                        $peserta = $record->pesertaKelas->first();
                        $idRegistrasi = $peserta?->id_registrasi_mahasiswa;

                        $record->load([
                            'pertemuanKelas.presensiMahasiswas' => function ($q) use ($idRegistrasi) {
                                $q->where('id_registrasi_mahasiswa', $idRegistrasi);
                            },
                        ]);

                        return view('filament.mahasiswa.resources.kelas-saya-resource.modals.riwayat-presensi', [
                            'record' => $record,
                        ]);
                    })
                    ->modalSubmitAction(false) // Hide submit button (view only)
                    ->modalCancelActionLabel('Tutup'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKelasSayas::route('/'),
        ];
    }
}

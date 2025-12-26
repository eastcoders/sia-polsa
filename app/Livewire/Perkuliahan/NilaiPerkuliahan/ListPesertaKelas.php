<?php

namespace App\Livewire\Perkuliahan\NilaiPerkuliahan;

use Livewire\Component;
use App\Models\SkalaNilai;
use Filament\Tables\Table;
use App\Models\KelasKuliah;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use App\Models\PesertaKelasKuliah;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use App\Jobs\PushNilaiPerkuliahanJob;
use App\Models\NilaiKelasPerkuliahan;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\SelectColumn;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextInputColumn;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class ListPesertaKelas extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public ?Model $record = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn() => PesertaKelasKuliah::with([
                    'riwayatPendidikan',
                    'kelasKuliah', // ← pastikan ini ada dan benar
                    'nilaiKuliah', // relasi ke NilaiKelasPerkuliahan
                ])
                    ->where('id_kelas_kuliah', $this->record->id_kelas_kuliah)
            )
            ->columns([
                TextColumn::make('id')->label('No.')->rowIndex(),
                TextColumn::make('riwayatPendidikan.nim')->label('NIM')->searchable(),
                TextColumn::make('riwayatPendidikan.mahasiswa.nama_lengkap')->label('Nama Lengkap')->searchable(),
                TextColumn::make('riwayatPendidikan.prodi.nama_program_studi')->label('Jurusan')->searchable(),
                TextColumn::make('riwayatPendidikan.periodeDaftar.id_tahun_ajaran')->label('Angkatan')->searchable(),
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

                // Kolom Nilai Angka
                TextInputColumn::make('nilai_angka')
                    ->label('Angka')
                    ->getStateUsing(fn(PesertaKelasKuliah $record) => $record->nilaiKuliah?->nilai_angka)
                    ->updateStateUsing(function (PesertaKelasKuliah $record, $state) {
                        DB::transaction(function () use ($record, $state) {
                            // Validasi
                            $angka = ($state !== '' && is_numeric($state)) ? (int) $state : null;

                            // Konversi ke huruf
                            $huruf = null;
                            if ($angka !== null) {
                                $huruf = $angka >= 80 ? 'A' :
                                    ($angka >= 70 ? 'B' :
                                        ($angka >= 60 ? 'C' :
                                            ($angka >= 50 ? 'D' : 'E')));
                            }

                            // Ambil id_prodi dari kelasKuliah
                            $idProdi = $record->kelasKuliah?->id_prodi;
                            if (!$idProdi) {
                                throw new \Exception('id_prodi tidak ditemukan.');
                            }

                            // Ambil nilai_indeks dari skala
                            $nilaiIndeks = null;
                            if ($huruf) {
                                $skala = SkalaNilai::where('id_prodi', $idProdi)
                                    ->where('nilai_huruf', $huruf)
                                    ->first();
                                $nilaiIndeks = $skala?->nilai_indeks;
                            }

                            // Simpan ke tabel nilai_kelas_perkuliahan
                            $record->nilaiKuliah()->updateOrCreate(
                                [
                                    'id_kelas_kuliah' => $record->id_kelas_kuliah,
                                    'id_registrasi_mahasiswa' => $record->id_registrasi_mahasiswa,
                                ],
                                [
                                    'nilai_angka' => $angka,
                                    'nilai_huruf' => $huruf,
                                    'nilai_indeks' => $nilaiIndeks,
                                    'sync_at' => null,
                                ]
                            );
                        });
                    })
                    ->rules(['nullable', 'integer', 'between:0,100']),

                // Kolom Nilai Huruf
                SelectColumn::make('nilai_huruf')
                    ->label('Huruf')
                    ->options(function (PesertaKelasKuliah $record) {
                        $idProdi = $record->kelasKuliah?->id_prodi;
                        if (!$idProdi)
                            return [];

                        return SkalaNilai::where('id_prodi', $idProdi)
                            ->orderBy('nilai_huruf')
                            ->get()
                            ->mapWithKeys(fn($skala) => [
                                $skala->nilai_huruf => $skala->skalaIndex,
                            ])
                            ->toArray();
                    })
                    ->getStateUsing(fn(PesertaKelasKuliah $record) => $record->nilaiKuliah?->nilai_huruf)
                    ->updateStateUsing(function (PesertaKelasKuliah $record, $state) {
                        DB::transaction(function () use ($record, $state) {
                            if ($state === null || $state === '') {
                                // Hapus atau null-kan?
                                $record->nilaiKuliah?->update([
                                    'nilai_huruf' => null,
                                    'nilai_indeks' => null,
                                    'nilai_angka' => null,
                                ]);
                                return;
                            }

                            $idProdi = $record->kelasKuliah?->id_prodi;
                            if (!$idProdi) {
                                throw new \Exception('id_prodi tidak ditemukan.');
                            }

                            $skala = SkalaNilai::where('id_prodi', $idProdi)
                                ->where('nilai_huruf', $state)
                                ->firstOrFail();

                            // Opsional: hitung perkiraan nilai_angka (atau biarkan null)
                            // Di sini kita biarkan null karena input manual lebih akurat
                            $record->nilaiKuliah()->updateOrCreate(
                                [
                                    'id_kelas_kuliah' => $record->id_kelas_kuliah,
                                    'id_registrasi_mahasiswa' => $record->id_registrasi_mahasiswa,
                                ],
                                [
                                    'nilai_huruf' => $state,
                                    'nilai_indeks' => $skala->nilai_indeks,
                                    'nilai_angka' => null, // atau hitung rentang, tapi biasanya tidak perlu
                                    'sync_at' => null,
                                ]
                            );
                        });
                    }),
            ])
            ->recordActions([
                Action::make('sync')
                    ->label('Sync')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Sync Nilai ke Server')
                    ->modalDescription('Apakah Anda yakin ingin sync nilai mahasiswa ini ke server?')
                    ->action(function (PesertaKelasKuliah $record) {
                        // Cari nilai dari record
                        $nilai = NilaiKelasPerkuliahan::where('id_kelas_kuliah', $record->id_kelas_kuliah)
                            ->where('id_registrasi_mahasiswa', $record->id_registrasi_mahasiswa)
                            ->first();

                        if (!$nilai) {
                            Notification::make()
                                ->title('Tidak ada nilai')
                                ->body('Mahasiswa ini belum memiliki nilai untuk di-sync.')
                                ->warning()
                                ->send();
                            return;
                        }

                        if ($nilai->nilai_huruf === null) {
                            Notification::make()
                                ->title('Nilai belum lengkap')
                                ->body('Nilai huruf belum diisi.')
                                ->warning()
                                ->send();
                            return;
                        }

                        // Dispatch job
                        PushNilaiPerkuliahanJob::dispatch($nilai);

                        Notification::make()
                            ->title('Job dispatched')
                            ->body('Proses sync nilai telah dijadwalkan.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(PesertaKelasKuliah $record) => $record->nilaiKuliah !== null),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('syncSelected')
                        ->label('Sync Terpilih')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Sync Nilai ke Server')
                        ->modalDescription('Apakah Anda yakin ingin sync semua nilai yang dipilih ke server?')
                        ->action(function (Collection $records) {
                            $dispatched = 0;
                            $skipped = 0;

                            foreach ($records as $record) {
                                $nilai = NilaiKelasPerkuliahan::where('id_kelas_kuliah', $record->id_kelas_kuliah)
                                    ->where('id_registrasi_mahasiswa', $record->id_registrasi_mahasiswa)
                                    ->first();

                                if (!$nilai || $nilai->nilai_huruf === null) {
                                    $skipped++;
                                    continue;
                                }

                                PushNilaiPerkuliahanJob::dispatch($nilai);
                                $dispatched++;
                            }

                            Notification::make()
                                ->title('Bulk Sync Selesai')
                                ->body("{$dispatched} job dispatched, {$skipped} dilewati (tidak ada nilai).")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.perkuliahan.nilai-perkuliahan.list-peserta-kelas');
    }
}

<?php

namespace App\Livewire\Perkuliahan\NilaiPerkuliahan;

use Livewire\Component;
use App\Models\SkalaNilai;
use Filament\Tables\Table;
use App\Models\KelasKuliah;
use App\Models\PesertaKelasKuliah;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\SelectColumn;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextInputColumn;
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
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.perkuliahan.nilai-perkuliahan.list-peserta-kelas');
    }
}

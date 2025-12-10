<?php

namespace App\Livewire\Perkuliahan\NilaiPerkuliahan;

use App\Models\PesertaKelasKuliah;
use App\Models\SkalaNilai;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ListPesertaKelas extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public ?Model $record = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => PesertaKelasKuliah::with('riwayatPendidikan')->where('id_kelas_kuliah', $this->record->id_kelas_kuliah))
            ->columns([
                TextColumn::make('id')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('riwayatPendidikan.nim')
                    ->label('NIM')
                    ->searchable(),
                TextColumn::make('riwayatPendidikan.mahasiswa.nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable(),
                TextColumn::make('riwayatPendidikan.prodi.nama_program_studi')
                    ->label('Jurusan')
                    ->searchable(),
                TextColumn::make('riwayatPendidikan.periodeDaftar.id_tahun_ajaran')
                    ->label('Angakatan')
                    ->searchable(),
                TextInputColumn::make('nilai_angka')
                    ->label('Angka')
                    ->state(fn (PesertaKelasKuliah $record) => $record->nilaiKelasPerkuliahan?->nilai_angka)
                    ->rules(['nullable', 'integer', 'between:0,100'])
                    ->updateStateUsing(function (PesertaKelasKuliah $record, $state) {
                        DB::transaction(function () use ($record, $state) {
                            // Konversi nilai angka ke huruf
                            $huruf = null;
                            if (is_numeric($state) && $state !== null && $state !== '') {
                                $angka = (int) $state;
                                $huruf = $angka >= 80 ? 'A' :
                                    ($angka >= 70 ? 'B' :
                                        ($angka >= 60 ? 'C' :
                                            ($angka >= 50 ? 'D' : 'E')));
                            }

                            $attributes = [
                                'id_kelas_kuliah' => $record->id_kelas_kuliah,
                                'id_registrasi_mahasiswa' => $record->id_registrasi_mahasiswa,
                            ];

                            $values = [
                                'nilai_angka' => $state !== '' ? $state : null,
                                'nilai_huruf' => $huruf,
                                'sync_at' => null,
                            ];

                            $record->nilaiKelasPerkuliahan()->updateOrCreate($attributes, $values);
                        });
                    }),
                SelectColumn::make('nilai_huruf')
                    ->label('Huruf')
                    ->options(function ($record) {
                        return SkalaNilai::where('id_prodi', $record->riwayatPendidikan->id_prodi)
                            ->orderBy('nilai_huruf')
                            ->get()
                            ->mapWithKeys(fn ($skala) => [
                                $skala->nilai_huruf => "{$skala->nilai_huruf} ({$skala->nilai_indeks})",
                            ])
                            ->toArray();
                    })
                    ->state(fn (PesertaKelasKuliah $record) => $record->nilaiKelasPerkuliahan?->nilai_huruf)
                    ->updateStateUsing(function (PesertaKelasKuliah $record, $state) {
                        DB::transaction(function () use ($record, $state) {
                            $idProdi = $record->riwayatPendidikan->id_prodi;

                            $skala = SkalaNilai::where('id_prodi', $idProdi)
                                ->where('nilai_huruf', $state)
                                ->firstOrFail();

                            $attributes = [
                                'id_kelas_kuliah' => $record->id_kelas_kuliah,
                                'id_registrasi_mahasiswa' => $record->id_registrasi_mahasiswa,
                            ];

                            $values = [
                                'nilai_huruf' => $state,
                                'nilai_indeks' => $skala->nilai_indeks,
                                'sync_at' => null,
                            ];

                            $record->nilaiKelasPerkuliahan()->updateOrCreate($attributes, $values);
                        });
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.perkuliahan.nilai-perkuliahan.list-peserta-kelas');
    }
}

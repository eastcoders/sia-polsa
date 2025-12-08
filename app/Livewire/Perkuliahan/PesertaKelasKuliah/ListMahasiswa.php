<?php

namespace App\Livewire\Perkuliahan\PesertaKelasKuliah;

use App\Models\Prodi;
use Livewire\Component;
use App\Models\Semester;
use Filament\Tables\Table;
use Filament\Actions\Action;
use App\Models\BiodataMahasiswa;
use Filament\Actions\BulkAction;
use App\Models\PesertaKelasKuliah;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class ListMahasiswa extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public string $id_kelas_kuliah;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => BiodataMahasiswa::with('riwayatPendidikan'))
            ->columns([
                TextColumn::make('id')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('riwayatPendidikan.nim')
                    ->label('NIM')
                    ->searchable(),
                TextColumn::make('nama_lengkap')
                    ->label('Nama Mahasiswa')
                    ->searchable(),
                TextColumn::make('riwayatPendidikan.prodi.programStudiLengkap')
                    ->label('Program Studi'),
                TextColumn::make('riwayatPendidikan.periodeDaftar.id_tahun_ajaran')
                    ->label('Angkatan'),
            ])
            ->filters([
                SelectFilter::make('prodi')
                    ->label('Program Studi')
                    ->options(
                        Prodi::query()
                            ->orderBy('nama_jenjang_pendidikan')
                            ->orderBy('nama_program_studi')
                            ->pluck('nama_program_studi', 'id_prodi')
                            ->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        $values = $data['values'] ?? $data['value'] ?? null;

                        if (!empty($values)) {
                            $query->whereHas('riwayatPendidikan', function (Builder $q) use ($values) {
                                $q->whereIn('id_prodi', (array) $values);
                            });
                        }

                        return $query;
                    }),

                SelectFilter::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->options(
                        Semester::query()
                            ->orderBy('id_tahun_ajaran', 'desc')
                            ->pluck('id_tahun_ajaran', 'id_tahun_ajaran')
                            ->unique()
                            ->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (!empty($value)) {
                            $query->whereHas('riwayatPendidikan.periodeDaftar', function (Builder $q) use ($value) {
                                $q->where('id_tahun_ajaran', $value);
                            });
                        }

                        return $query;
                    }),
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                BulkAction::make('addSelected')
                    ->label('Tambahkan Peserta Terpilih')
                    ->action(function ($records) {
                        $this->addSelectedByRecords($records);
                    })
                    ->requiresConfirmation()
                // ]),
            ])
            ->checkIfRecordIsSelectableUsing(function ($record): bool {
                // Ambil id_registrasi_mahasiswa dari relasi
                $idRegis = $record->riwayatPendidikan?->id_registrasi_mahasiswa;

                // Jika sudah terdaftar, maka jangan biarkan user memilihnya lagi
                if ($idRegis) {
                    $exists = DB::table('peserta_kelas_kuliahs')
                        ->where('id_kelas_kuliah', $this->id_kelas_kuliah)
                        ->where('id_registrasi_mahasiswa', $idRegis)
                        ->exists();

                    return !$exists; // Jika sudah ada, maka tidak bisa dipilih
                }

                return true; // Jika tidak ada id_registrasi, boleh dipilih (jika perlu)
            });
    }

    public function render(): View
    {
        return view('livewire.perkuliahan.peserta-kelas-kuliah.list-mahasiswa');
    }

    public function addSelectedByRecords($records)
    {
        $idRegistrasiMahasiswa = $records->pluck('riwayatPendidikan.id_registrasi_mahasiswa')->filter()->toArray();

        if (empty($idRegistrasiMahasiswa)) {
            Notification::make()
                ->title('Gagal')
                ->body('Tidak ada ID registrasi mahasiswa yang valid untuk ditambahkan.')
                ->danger()
                ->send();

            return;
        }

        try {
            DB::transaction(function () use ($idRegistrasiMahasiswa) {
                foreach ($idRegistrasiMahasiswa as $id_regis_mahasiswa) {
                    PesertaKelasKuliah::create([
                        'id_registrasi_mahasiswa' => $id_regis_mahasiswa,
                        'id_kelas_kuliah' => $this->id_kelas_kuliah,
                    ]);
                }
            });

            Notification::make()
                ->title('Berhasil')
                ->body(count($idRegistrasiMahasiswa) . ' mahasiswa berhasil ditambahkan sebagai peserta kelas.')
                ->success()
                ->actions([
                    Action::make('Tutup')
                        ->close()
                ])
                ->send();


        } catch (\Throwable $e) {
            DB::rollBack(); // Ini sebenarnya tidak perlu karena DB::transaction otomatis rollback

            Log::error('Gagal menambahkan peserta kelas: ' . $e->getMessage(), [
                'id_kelas_kuliah' => $this->id_kelas_kuliah,
                'id_registrasi_mahasiswa' => $idRegistrasiMahasiswa,
            ]);

            Notification::make()
                ->title('Gagal')
                ->body('Terjadi kesalahan saat menambahkan peserta. Silakan coba lagi.')
                ->danger()
                ->send();
        }
    }


}

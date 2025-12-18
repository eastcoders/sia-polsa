<?php

namespace App\Livewire\Perkuliahan;

use App\Models\KelasKuliah;
use App\Models\PesertaKelasKuliah;
use App\Models\Prodi;
use App\Models\RiwayatPendidikan;
use App\Models\Semester;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ListKelasKuliah extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public string $id_registrasi_mahasiswa;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => KelasKuliah::with(['matkul', 'semester', 'prodi']))
            ->columns([
                TextColumn::make('matkul.kode_mata_kuliah')
                    ->label('Kode MK')
                    ->searchable(),
                TextColumn::make('matkul.nama_mata_kuliah')
                    ->label('Nama Mata Kuliah')
                    ->searchable(),
                TextColumn::make('nama_kelas_kuliah')
                    ->label('Nama Kelas')
                    ->searchable(),
                TextColumn::make('sks_mk')
                    ->label('SKS'),
                TextColumn::make('semester.nama_semester')
                    ->label('Semester'),
                TextColumn::make('prodi.programStudiLengkap')
                    ->label('Program Studi')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('prodi')
                    ->label('Program Studi')
                    ->options(
                        Prodi::query()
                            ->orderBy('nama_jenjang_pendidikan')
                            ->orderBy('nama_program_studi')
                            ->pluck('nama_program_studi', 'id_prodi')
                            ->unique() // Just in case, though names should be unique enough or use ID
                            ->toArray()
                    )
                    ->getOptionLabelFromRecordUsing(fn(Prodi $record) => $record->programStudiLengkap) // Filament 3 style but for arrays we use options() usually.
                    // Let's stick to options logic from reference:
                    // pluck('nama_program_studi', 'id_prodi') might be ambiguous if names are same.
                    // Reference ListMahasiswa used pluck('nama_program_studi', 'id_prodi').
                    // But here I'll try to match the student's prodi default.
                    ->default(function () {
                        $riwayat = RiwayatPendidikan::find($this->id_registrasi_mahasiswa);
                        return $riwayat?->id_prodi;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        if (!empty($value)) {
                            $query->where('id_prodi', $value);
                        }
                        return $query;
                    }),

                SelectFilter::make('semester')
                    ->label('Semester')
                    ->options(
                        Semester::query()
                            ->orderBy('id_tahun_ajaran', 'desc')
                            ->pluck('nama_semester', 'id_semester')
                            ->toArray()
                    )
                    ->default(function () {
                        // Default to active semester
                        return Semester::where('a_periode_aktif', '1')
                            ->where('id_tahun_ajaran', '>=', now()->year)
                            ->orderBy('id_tahun_ajaran', 'asc')
                            ->first()
                                ?->id_semester;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        if (!empty($value)) {
                            $query->where('id_semester', $value);
                        }
                        return $query;
                    }),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([
                BulkAction::make('addSelected')
                    ->label('Ambil Kelas Terpilih')
                    ->action(function ($records) {
                        $this->addSelectedByRecords($records);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pengambilan Kelas')
                    ->modalDescription('Apakah Anda yakin ingin mengambil kelas-kelas terpilih? Total SKS akan divalidasi.'),
            ])
            ->checkIfRecordIsSelectableUsing(function ($record): bool {
                // Check if already taken
                $exists = PesertaKelasKuliah::where('id_registrasi_mahasiswa', $this->id_registrasi_mahasiswa)
                    ->where('id_kelas_kuliah', $record->id_kelas_kuliah)
                    ->exists();
                return !$exists;
            });
    }

    public function render(): View
    {
        return view('livewire.perkuliahan.list-kelas-kuliah');
    }

    public function addSelectedByRecords($records)
    {
        $newSks = $records->sum('sks_mk');

        // Ensure all records belong to the same semester if necessary, 
        // or just calculate SKS per semester. 
        // Usually KRS is for ONE specific semester (the active one).
        // But the table filter lets you select any semester.
        // If user selects classes from Semester A and Semester B simultaneously, validation gets tricky.
        // I will assume for now we validate based on the semester of the *active filter* or the class itself.

        // Safe approach: aggregate SKS by semester for the NEW classes
        $newClassesBySemester = $records->groupBy('id_semester');

        try {
            DB::transaction(function () use ($newClassesBySemester, $records) {
                foreach ($newClassesBySemester as $idSemester => $classes) {
                    // 1. Calculate existing SKS for this student in this semester
                    $existingSks = PesertaKelasKuliah::where('id_registrasi_mahasiswa', $this->id_registrasi_mahasiswa)
                        ->whereHas('kelasKuliah', function ($q) use ($idSemester) {
                            $q->where('id_semester', $idSemester);
                        })
                        ->get()
                        ->sum(fn($peserta) => $peserta->kelasKuliah->sks_mk ?? 0);

                    $semesterSksToAdd = $classes->sum('sks_mk');
                    $totalSks = $existingSks + $semesterSksToAdd;

                    if ($totalSks > 24) {
                        throw new \Exception("Total SKS untuk semester ini akan melebihi 24 SKS (Total: $totalSks). Transaksi dibatalkan.");
                    }
                }

                // If validation passed, insert
                foreach ($records as $kelas) {
                    PesertaKelasKuliah::create([
                        'id_registrasi_mahasiswa' => $this->id_registrasi_mahasiswa,
                        'id_kelas_kuliah' => $kelas->id_kelas_kuliah,
                    ]);
                }
            });

            Notification::make()
                ->title('Berhasil')
                ->body(count($records) . ' kelas berhasil ditambahkan.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (\Throwable $e) {
            Log::error('Gagal mengambil kelas: ' . $e->getMessage());
            Notification::make()
                ->title('Gagal')
                ->body('Terjadi kesalahan sistem.')
                ->danger()
                ->send();
        }
    }
}

<?php

namespace App\Filament\Resources\BiodataMahasiswas\Pages;

use App\Filament\Resources\BiodataMahasiswas\BiodataMahasiswaResource;
use BackedEnum;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Support\Icons\Heroicon;

class HistoriNilaiMahasiswa extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = BiodataMahasiswaResource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected string $view = 'filament.resources.biodata-mahasiswas.pages.histori-nilai-mahasiswa';

    public static function getNavigationLabel(): string
    {
        return 'Histori Nilai Mahasiswa';
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->query(
                \App\Models\NilaiKelasPerkuliahan::query()
                    ->join('kelas_kuliahs', 'nilai_kelas_perkuliahans.id_kelas_kuliah', '=', 'kelas_kuliahs.id_kelas_kuliah')
                    ->join('semesters', 'kelas_kuliahs.id_semester', '=', 'semesters.id_semester')
                    ->where('nilai_kelas_perkuliahans.id_registrasi_mahasiswa', $this->record->riwayatPendidikan?->id_registrasi_mahasiswa)
                    ->select('nilai_kelas_perkuliahans.*')
                    ->with([
                        'kelasKuliah' => fn($query) => $query->withTrashed()->with(['matkul', 'semester']),
                    ])
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('kelasKuliah.semester.nama_semester')
                    ->label('Semester')
                    ->sortable()
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('kelasKuliah.matkul.kode_mata_kuliah')
                    ->label('Kode MK')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('kelasKuliah.matkul.nama_mata_kuliah')
                    ->label('Nama Mata Kuliah')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('kelasKuliah.sks_mk')
                    ->label('SKS'),
                \Filament\Tables\Columns\TextColumn::make('nilai_angka')
                    ->label('Nilai Angka'),
                \Filament\Tables\Columns\TextColumn::make('nilai_huruf')
                    ->label('Nilai Huruf'),
                \Filament\Tables\Columns\TextColumn::make('nilai_indeks')
                    ->label('Indeks'),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('semester')
                    ->label('Filter Semester')
                    ->native(false)
                    ->options(
                        \App\Models\Semester::query()
                            ->where('id_tahun_ajaran', '<=', now()->year . '2')
                            ->orderBy('id_tahun_ajaran', 'desc')
                            ->pluck('nama_semester', 'id_semester')
                            ->toArray()
                    )
                    ->default(fn() => session('active_semester_id') ?? \App\Models\Semester::where('a_periode_aktif', 1)->value('id_semester'))
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        $value = $data['value'] ?? null;
                        if (!empty($value)) {
                            // Specify table name to avoid ambiguity
                            $query->where('semesters.id_semester', $value);
                        }
                        return $query;
                    }),
            ])
            ->defaultSort('semesters.id_semester', 'desc');
    }
}

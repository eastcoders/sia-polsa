<?php

namespace App\Filament\Dosen\Pages;

use App\Models\KelasKuliah;
use App\Models\KomponenBobotKelas;
use App\Models\NilaiEvaluasiAkhir;
use App\Models\NilaiTugas;
use App\Models\PesertaKelasKuliah;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InputNilai extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-pencil-square';

    protected string $view = 'filament.dosen.pages.input-nilai';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Rekapitulasi Nilai Akhir';

    public $record_id;

    public $kelas;

    public function mount()
    {
        $this->record_id = request()->query('record');

        if (! $this->record_id) {
            abort(404, 'Kelas tidak ditemukan.');
        }

        $this->kelas = KelasKuliah::with(['semester', 'prodi', 'matkul'])
            ->where('id_kelas_kuliah', $this->record_id)
            ->firstOrFail();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PesertaKelasKuliah::query()
                    ->where('id_kelas_kuliah', $this->record_id)
                    ->with(['riwayatPendidikan.mahasiswa'])
            )
            ->columns([
                TextColumn::make('nama_lengkap')
                    ->label('Mahasiswa')
                    ->state(fn (Model $record) => $record->riwayatPendidikan->mahasiswa->nama_lengkap ?? '-')
                    ->description(fn (Model $record) => $record->riwayatPendidikan->nim ?? '-')
                    ->searchable(query: fn ($query, $search) => $query->whereHas('riwayatPendidikan.mahasiswa', fn ($q) => $q->where('nama_lengkap', 'like', "%{$search}%")))
                    ->sortable(),

                // --- READ ONLY COLUMNS FROM MENU A ---
                TextColumn::make('presensi_pct')
                    ->label('Presensi (%)')
                    ->state(fn (Model $record) => $this->calculatePresensiPercentage($record).'%')
                    ->color('gray')
                    ->icon('heroicon-o-lock-closed')
                    ->tooltip('Data diambil dari Jurnal Perkuliahan'),

                TextColumn::make('avg_tugas')
                    ->label('Rata Tugas')
                    ->state(function (Model $record) {
                        $val = $this->calculateAvgTugas($record);

                        return $val > 0 ? number_format($val, 2) : '-';
                    })
                    ->color('gray')
                    ->icon('heroicon-o-lock-closed')
                    ->tooltip('Rata-rata dari nilai tugas di Jurnal'),

                // --- SUMMATIVE COLUMNS (Editable) ---
                TextInputColumn::make('uts')
                    ->label('UTS')
                    ->type('number')->rules(['numeric', 'min:0', 'max:100'])
                    ->state(fn ($record) => $this->getNilaiAkhir($record, 'UTS'))
                    ->updateStateUsing(fn ($record, $state) => $this->updateNilaiAkhir($record, 'UTS', $state)),

                TextInputColumn::make('uas')
                    ->label('UAS')
                    ->type('number')->rules(['numeric', 'min:0', 'max:100'])
                    ->state(fn ($record) => $this->getNilaiAkhir($record, 'UAS'))
                    ->updateStateUsing(fn ($record, $state) => $this->updateNilaiAkhir($record, 'UAS', $state)),

                TextInputColumn::make('etika')
                    ->label('Etika')
                    ->type('number')->rules(['numeric', 'min:0', 'max:100'])
                    ->state(fn ($record) => $this->getNilaiAkhir($record, 'Etika'))
                    ->updateStateUsing(fn ($record, $state) => $this->updateNilaiAkhir($record, 'Etika', $state)),

                TextInputColumn::make('keaktifan')
                    ->label('Keaktifan')
                    ->type('number')->rules(['numeric', 'min:0', 'max:100'])
                    ->state(fn ($record) => $this->getNilaiAkhir($record, 'Keaktifan'))
                    ->updateStateUsing(fn ($record, $state) => $this->updateNilaiAkhir($record, 'Keaktifan', $state)),

                // --- FINAL SCORE ---
                TextColumn::make('nilai_akhir')
                    ->label('NA (Angka)')
                    ->state(fn (Model $record) => $this->calculateFinalScore($record))
                    ->weight('bold')
                    ->color('success'),
            ])
            ->paginated(false);
    }

    // --- Helpers ---

    protected function calculatePresensiPercentage($record)
    {
        // Cache total pertemuan per request? Not strictly necessary for low load, but logic:
        $totalPertemuan = $this->kelas->pertemuanKelas()->count();
        if ($totalPertemuan == 0) {
            return 0;
        }

        $hadir = $record->kelasKuliah->pertemuanKelas()
            ->whereHas('presensiMahasiswas', function ($q) use ($record) {
                $q->where('id_registrasi_mahasiswa', $record->id_registrasi_mahasiswa)
                    ->where('status_kehadiran', 'hadir');
            })->count();

        return round(($hadir / $totalPertemuan) * 100);
    }

    protected function calculateAvgTugas($record)
    {
        // Get all graded tasks for this user directly from DB
        return NilaiTugas::whereHas('tugasPertemuan.pertemuanKelas', function ($q) {
            $q->where('id_kelas_kuliah', $this->record_id);
        })
            ->where('id_registrasi_mahasiswa', $record->id_registrasi_mahasiswa)
            ->avg('nilai') ?? 0;
    }

    protected function getNilaiAkhir($record, $jenis)
    {
        return NilaiEvaluasiAkhir::where('id_kelas_kuliah', $this->record_id)
            ->where('id_registrasi_mahasiswa', $record->id_registrasi_mahasiswa)
            ->where('jenis_nilai', $jenis)
            ->value('nilai');
    }

    protected function updateNilaiAkhir($record, $jenis, $state)
    {
        // Validation Active Semester
        if ($this->kelas->semester->a_periode_aktif != '1') {
            Notification::make()->title('Gagal')->body('Semester tidak aktif.')->danger()->send();

            return;
        }

        NilaiEvaluasiAkhir::updateOrCreate(
            [
                'id_kelas_kuliah' => $this->record_id,
                'id_registrasi_mahasiswa' => $record->id_registrasi_mahasiswa,
                'jenis_nilai' => $jenis,
            ],
            ['nilai' => $state ?? 0]
        );
    }

    protected function calculateFinalScore($record)
    {
        static $weights = null;
        if (! $weights) {
            $weights = KomponenBobotKelas::where('id_kelas_kuliah', $this->record_id)
                ->pluck('bobot', 'nama_komponen');
        }

        $score = 0;

        // 1. Tugas (25%) -> Based on live AVG
        $avgTugas = $this->calculateAvgTugas($record);
        $score += $avgTugas * (($weights['Tugas'] ?? 0) / 100);

        // 2. Presensi (15%) -> Based on live Attendance
        $presensiPct = $this->calculatePresensiPercentage($record);
        $score += $presensiPct * (($weights['Presensi'] ?? 0) / 100);

        // 3. Fixed Components
        foreach (['UTS', 'UAS', 'Etika', 'Keaktifan'] as $comp) {
            $val = $this->getNilaiAkhir($record, $comp) ?? 0;
            $score += $val * (($weights[$comp] ?? 0) / 100);
        }

        return number_format($score, 2);
    }
}

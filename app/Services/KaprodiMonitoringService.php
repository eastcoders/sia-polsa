<?php

namespace App\Services;

use App\Models\KelasKuliah;
use App\Models\Dosen;
use App\Models\RiwayatPendidikan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class KaprodiMonitoringService
{
    protected array $prodiIds;

    /**
     * Constructor: Otomatis mendeteksi User Login & Prodi yang dikelola.
     */
    public function __construct()
    {
        $user = Auth::user();

        // Cek jika user valid kaprodi & punya data dosen
        if ($user && $user->dosen) {
            $this->prodiIds = $user->dosen->memimpinProdi()->pluck('id_prodi')->toArray();
        } else {
            $this->prodiIds = [];
        }
    }

    /**
     * METRIK 1: Operational Hazard (Kelas Bermasalah)
     */
    public function getKelasBermasalahQuery(?string $semesterId = null): Builder
    {
        $query = KelasKuliah::query()->whereIn('id_prodi', $this->prodiIds);

        if ($semesterId) {
            $query->where('id_semester', $semesterId);
        } else {
            // Default Active Semester if not provided (Safety net)
            // Atau biarkan global jika null? Tapi untuk monitoring sebaiknya snapshot per semester.
            $activeSemesterId = \App\Models\Semester::where('a_periode_aktif', '1')->value('id_semester');
            $query->where('id_semester', $activeSemesterId);
        }

        return $query
            ->where(function ($query) {
                $query->whereDoesntHave('dosenPengajarKelasKuliah')
                    ->orWhereDoesntHave('jadwalPerkuliahan');
            })
            ->with(['matkul', 'semester']);
    }

    /**
     * REVISI METRIK 2: Kelas dengan Realisasi Pertemuan Rendah
     */
    public function getKelasLowAttendanceQuery(int $limitWarning = 4, ?string $semesterId = null): Builder
    {
        $query = KelasKuliah::query()->whereIn('id_prodi', $this->prodiIds);

        if ($semesterId) {
            $query->where('id_semester', $semesterId);
        } else {
            $activeSemesterId = \App\Models\Semester::where('a_periode_aktif', '1')->value('id_semester');
            $query->where('id_semester', $activeSemesterId);
        }

        return $query
            ->withCount('pertemuanKelas')
            ->having('pertemuan_kelas_count', '<', $limitWarning)
            ->with(['matkul', 'dosenPengajarKelasKuliah.dosen']);
    }

    /**
     * METRIK 3: Student Risk (Mahasiswa Kritis)
     */
    public function getMahasiswaKritisQuery(?string $semesterId = null): Builder
    {
        // 1. Tentukan Semester ID
        $targetSemesterId = $semesterId ?? cache()->remember('current_active_semester_id', 60 * 60, function () {
            return \App\Models\Semester::where('a_periode_aktif', '1')->value('id_semester');
        });

        // 2. Query ke tabel Aktivitas (Snapshot Performa)
        return \App\Models\AktivitasKuliahMahasiswa::query()
            ->whereHas('riwayatPendidikan', function ($query) {
                $query->whereIn('id_prodi', $this->prodiIds);
            })
            ->where('id_semester', $targetSemesterId)
            ->where('id_status_mahasiswa', 'A')
            ->where('ipk', '<', 2.00)
            ->where('ipk', '>', 0)
            ->with(['riwayatPendidikan.mahasiswa']);
    }
}

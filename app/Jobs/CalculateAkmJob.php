<?php

namespace App\Jobs;

use App\Models\AktivitasKuliahMahasiswa;
use App\Models\NilaiKelasPerkuliahan;
use App\Models\RiwayatPendidikan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CalculateAkmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $id_registrasi_mahasiswa
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $riwayat = RiwayatPendidikan::where('id_registrasi_mahasiswa', $this->id_registrasi_mahasiswa)->firstOrFail();

        // Ambil semua nilai mahasiswa ini, urutkan berdasarkan semester
        $nilais = NilaiKelasPerkuliahan::with(['kelasKuliah.matkul', 'kelasKuliah.semester'])
            ->where('id_registrasi_mahasiswa', $this->id_registrasi_mahasiswa)
            ->get()
            ->groupBy(fn($item) => $item->kelasKuliah->id_semester)
            ->sortKeys();

        $totalSksKumulatif = 0;
        $totalMutuKumulatif = 0;

        DB::transaction(function () use ($nilais, $riwayat, &$totalSksKumulatif, &$totalMutuKumulatif) {
            foreach ($nilais as $idSemester => $groupNilai) {
                // Kalkulasi Semester Ini
                $sksSemester = 0;
                $mutuSemester = 0;

                foreach ($groupNilai as $nilai) {
                    $sks = $nilai->kelasKuliah->matkul->sks_mata_kuliah ?? 0;
                    $indeks = $nilai->nilai_indeks ?? 0;

                    $sksSemester += $sks;
                    $mutuSemester += ($sks * $indeks);
                }

                $ips = $sksSemester > 0 ? round($mutuSemester / $sksSemester, 2) : 0;

                // Update Kumulatif
                $totalSksKumulatif += $sksSemester;
                $totalMutuKumulatif += $mutuSemester;
                $ipk = $totalSksKumulatif > 0 ? round($totalMutuKumulatif / $totalSksKumulatif, 2) : 0;

                // Simpan ke AKM
                AktivitasKuliahMahasiswa::updateOrCreate(
                    [
                        'id_registrasi_mahasiswa' => $this->id_registrasi_mahasiswa,
                        'id_semester' => $idSemester,
                    ],
                    [
                        'id_status_mahasiswa' => 'A', // Default Aktif jika ada nilai
                        'ips' => $ips,
                        'ipk' => $ipk,
                        'sks_semester' => $sksSemester,
                        'sks_total' => $totalSksKumulatif,
                        'biaya_kuliah_smt' => 0, // Placeholder
                    ]
                );

                // Optional: Dispatch Push Job here if needed immediately
                // PushAktivitasKuliahMahasiswaJob::dispatch(...);
            }
        });

        Log::info("CalculateAkmJob: Selesai menghitung AKM untuk {$riwayat->nim}");
    }
}

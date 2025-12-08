<?php

namespace App\Jobs;

use App\Models\PenugasanDosen;
use App\Services\PddiktiClient;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class SyncPenugasanDosenJob implements ShouldQueue
{
    use Queueable;

    public $filter = [];

    /**
     * Create a new job instance.
     */
    public function __construct(array $filter = [])
    {
        $this->filter = $filter;
    }

    /**
     * Execute the job.
     */
    public function handle(PddiktiClient $client): void
    {
        $data = $client->getListPenugasanDosen($this->filter);

        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                $tanggalSuratTugas = $row['tanggal_surat_tugas'] ?? null;
                $mulaiSuratTugas = $row['mulai_surat_tugas'] ?? null;

                $tanggalSuratTugasFormatted = $tanggalSuratTugas ? Carbon::createFromFormat('d-m-Y', $tanggalSuratTugas)->format('Y-m-d') : null;
                $mulaiSuratTugasFormatted = $mulaiSuratTugas ? Carbon::createFromFormat('d-m-Y', $mulaiSuratTugas)->format('Y-m-d') : null;

                PenugasanDosen::updateOrCreate(
                    [
                        'id_registrasi_dosen' => $row['id_registrasi_dosen'],
                    ],
                    [
                        'id_dosen' => $row['id_dosen'],
                        'id_prodi' => $row['id_prodi'],
                        'id_tahun_ajaran' => $row['id_tahun_ajaran'],
                        'id_perguruan_tinggi' => $row['id_perguruan_tinggi'],
                        'nomor_surat_tugas' => $row['nomor_surat_tugas'],
                        'tanggal_surat_tugas' => $tanggalSuratTugasFormatted,
                        'mulai_surat_tugas' => $mulaiSuratTugasFormatted,
                        'sync_at' => now(),
                    ]
                );
            }
        });
    }
}

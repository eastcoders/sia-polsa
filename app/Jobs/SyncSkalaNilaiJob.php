<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\SkalaNilai;
use App\Services\PddiktiClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncSkalaNilaiJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(PddiktiClient $client): void
    {
        $data = $client->getListSkalaNilaiProdi();

        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                SkalaNilai::updateOrCreate(
                    [
                        'id_bobot_nilai' => trim($row['id_bobot_nilai']),
                    ],
                    [
                        'nilai_huruf' => trim($row['nilai_huruf']),
                        'id_prodi' => trim($row['id_prodi']),
                        'nilai_indeks' => trim($row['nilai_indeks']),
                        'bobot_nilai_min' => $row['bobot_nilai_min'],
                        'bobot_nilai_maks' => $row['bobot_nilai_maks'],
                        'tanggal_mulai_efektif' => Carbon::createFromFormat('d-m-Y', $row['tanggal_mulai_efektif'])->format('Y-m-d'),
                        'tanggal_akhir_efektif' => Carbon::createFromFormat('d-m-Y', $row['tanggal_akhir_efektif'])->format('Y-m-d'),
                        'sync_at' => now(),
                    ]
                );
            }
        });
    }
}

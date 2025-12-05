<?php

namespace App\Jobs;

use App\Models\Dosen;
use App\Services\PddiktiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class SyncDosenJob implements ShouldQueue
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
        $data = $client->getListDosen();

        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                Dosen::updateOrCreate(
                    [
                        'id_dosen' => $row['id_dosen'],
                    ],
                    [
                        'nama_dosen' => trim($row['nama_dosen']),
                        'nidn' => trim($row['nidn']),
                        'nip' => trim($row['nip']),
                        'jenis_kelamin' => trim($row['jenis_kelamin']),
                        'id_agama' => trim($row['id_agama']),
                        'tanggal_lahir' => trim($row['tanggal_lahir']),
                        'id_status_aktif' => trim($row['id_status_aktif']),
                        'nama_status_aktif' => trim($row['nama_status_aktif']),
                        'sync_at' => now(),
                    ]
                );
            }
        });
    }
}

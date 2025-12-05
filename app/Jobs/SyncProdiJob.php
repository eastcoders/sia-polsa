<?php

namespace App\Jobs;

use App\Models\Prodi;
use App\Services\PddiktiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class SyncProdiJob implements ShouldQueue
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
        $data = $client->getProdi();

        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                Prodi::updateOrCreate(
                    [
                        'id_prodi' => trim($row['id_prodi']),
                    ],
                    [
                        'nama_program_studi' => trim($row['nama_program_studi']),
                        'kode_program_studi' => $row['kode_program_studi'],
                        'status' => $row['status'],
                        'id_jenjang_pendidikan' => $row['id_jenjang_pendidikan'],
                        'nama_jenjang_pendidikan' => trim($row['nama_jenjang_pendidikan']),
                        'sync_at' => now(),
                    ]
                );
            }
        });
    }
}

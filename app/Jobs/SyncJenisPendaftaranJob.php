<?php

namespace App\Jobs;

use App\Models\JenisPendaftaran;
use App\Services\PddiktiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class SyncJenisPendaftaranJob implements ShouldQueue
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
        $data = $client->getJenisPendaftaran();

        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                JenisPendaftaran::updateOrCreate(
                    [
                        'id_jenis_daftar' => trim($row['id_jenis_daftar']),
                    ],
                    [
                        'nama_jenis_daftar' => trim($row['nama_jenis_daftar']),
                        'sync_at' => now(),
                    ]
                );
            }
        });
    }
}

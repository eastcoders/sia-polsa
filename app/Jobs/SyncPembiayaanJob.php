<?php

namespace App\Jobs;

use App\Models\Pembiayaan;
use App\Services\PddiktiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class SyncPembiayaanJob implements ShouldQueue
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
        $data = $client->getPembiayaan();

        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                Pembiayaan::updateOrCreate(
                    [
                        'id_pembiayaan' => trim($row['id_pembiayaan']),
                    ],
                    [
                        'nama_pembiayaan' => trim($row['nama_pembiayaan']),
                        'sync_at' => now(),
                    ]
                );
            }
        });
    }
}

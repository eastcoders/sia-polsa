<?php

namespace App\Jobs;

use App\Models\JalurMasuk;
use App\Services\PddiktiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class SyncJalurMasukJob implements ShouldQueue
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
        $data = $client->getJalurMasuk();

        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                JalurMasuk::updateOrCreate(
                    [
                        'id_jalur_masuk' => trim($row['id_jalur_masuk']),
                    ],
                    [
                        'nama_jalur_masuk' => trim($row['nama_jalur_masuk']),
                        'sync_at' => now(),
                    ]
                );
            }
        });
    }
}

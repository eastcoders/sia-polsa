<?php

namespace App\Jobs;

use App\Models\Wilayah;
use App\Services\PddiktiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SyncWilayahJob implements ShouldQueue
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
        $data = $client->getWilayah();

        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                Wilayah::updateOrCreate(
                    [
                        'id_wilayah' => trim($row['id_wilayah']),
                    ],
                    [
                        'id_negara' => trim($row['id_negara']),
                        'nama_wilayah' => $row['nama_wilayah'],
                        'id_level_wilayah' => $row['id_level_wilayah'],
                        'id_induk_wilayah' => trim($row['id_induk_wilayah']),
                        'sync_at' => now(),
                    ]
                );
            }
        });

        // simpan pesan sukses ke session
        Session::flash('success', 'Sync wilayah selesai. Total: '.count($data).' data.');
    }
}

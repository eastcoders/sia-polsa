<?php

namespace App\Jobs;

use App\Models\Agama;
use App\Services\PddiktiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SyncAgamaJob implements ShouldQueue
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
        $data = $client->getAgama();

        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                Agama::updateOrCreate(
                    [
                        'id_agama' => $row['id_agama'],
                    ],
                    [
                        'nama_agama' => $row['nama_agama'],
                        'sync_at' => now(),
                    ]
                );
            }
        });

        // simpan pesan sukses ke session
        Session::flash('success', 'Sync agama selesai. Total: '.count($data).' data.');
    }
}

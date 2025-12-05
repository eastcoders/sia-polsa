<?php

namespace App\Jobs;

use App\Models\AlatTransportasi;
use App\Services\PddiktiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SyncAlatTransportasiJob implements ShouldQueue
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
        $data = $client->getAlatTransportasi();

        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                AlatTransportasi::updateOrCreate(
                    [
                        'id_alat_transportasi' => $row['id_alat_transportasi'],
                    ],
                    [
                        'nama_alat_transportasi' => $row['nama_alat_transportasi'],
                        'sync_at' => now(),
                    ]
                );
            }
        });

        // simpan pesan sukses ke session
        Session::flash('success', 'Sync Alat Transportasi selesai. Total: '.count($data).' data.');
    }
}

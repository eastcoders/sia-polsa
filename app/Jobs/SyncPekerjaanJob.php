<?php

namespace App\Jobs;

use App\Models\Pekerjaan;
use App\Services\PddiktiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SyncPekerjaanJob implements ShouldQueue
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
        $data = $client->getPekerjaan();

        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                Pekerjaan::updateOrCreate(
                    [
                        'id_pekerjaan' => $row['id_pekerjaan'],
                    ],
                    [
                        'nama_pekerjaan' => $row['nama_pekerjaan'],
                        'sync_at' => now(),
                    ]
                );
            }
        });

        // simpan pesan sukses ke session
        Session::flash('success', 'Sync pekerjaan selesai. Total: '.count($data).' data.');
    }
}

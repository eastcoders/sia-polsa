<?php

namespace App\Jobs;

use App\Models\Penghasilan;
use App\Services\PddiktiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SyncPenghasilanJob implements ShouldQueue
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
        $data = $client->getPenghasilan();

        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                Penghasilan::updateOrCreate(
                    [
                        'id_penghasilan' => $row['id_penghasilan'],
                    ],
                    [
                        'nama_penghasilan' => $row['nama_penghasilan'],
                        'sync_at' => now(),
                    ]
                );
            }
        });

        // simpan pesan sukses ke session
        Session::flash('success', 'Sync penghasilan selesai. Total: '.count($data).' data.');
    }
}

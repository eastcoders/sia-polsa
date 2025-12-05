<?php

namespace App\Jobs;

use App\Models\JenisTinggal;
use App\Services\PddiktiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SyncJenisTinggalJob implements ShouldQueue
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
        $data = $client->getJenisTinggal();

        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                JenisTinggal::updateOrCreate(
                    [
                        'id_jenis_tinggal' => $row['id_jenis_tinggal'],
                    ],
                    [
                        'nama_jenis_tinggal' => $row['nama_jenis_tinggal'],
                        'sync_at' => now(),
                    ]
                );
            }
        });

        // simpan pesan sukses ke session
        Session::flash('success', 'Sync jenis tinggal selesai. Total: '.count($data).' data.');
    }
}

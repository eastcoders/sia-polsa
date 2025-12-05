<?php

namespace App\Jobs;

use App\Models\JenjangPendidikan;
use App\Services\PddiktiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SyncJenjangPendidikanJob implements ShouldQueue
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
        $data = $client->getJenjangPendidikan();

        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                JenjangPendidikan::updateOrCreate(
                    [
                        'id_jenjang_didik' => $row['id_jenjang_didik'],
                    ],
                    [
                        'nama_jenjang_didik' => $row['nama_jenjang_didik'],
                        'sync_at' => now(),
                    ]
                );
            }
        });

        // simpan pesan sukses ke session
        Session::flash('success', 'Sync pendidikan selesai. Total: '.count($data).' data.');
    }
}

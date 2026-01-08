<?php

namespace App\Jobs;

use App\Models\PerguruanTinggi;
use App\Services\PddiktiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class SyncAllPtJob implements ShouldQueue
{
    use \Illuminate\Bus\Batchable, Queueable;

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
        $data = collect($client->getAllPerguruanTinggi());

        // Process in chunks locally since we fetched all
        $data->chunk(1000)->each(function ($chunk) {
            $rows = $chunk->map(function ($row) {
                return [
                    'id_perguruan_tinggi' => trim($row['id_perguruan_tinggi']),
                    'nama_perguruan_tinggi' => trim($row['nama_perguruan_tinggi']),
                    'nama_singkat' => trim($row['nama_singkat']),
                    'kode_perguruan_tinggi' => $row['kode_perguruan_tinggi'],
                    'sync_at' => now(),
                ];
            })->toArray();

            DB::table('perguruan_tinggis')->upsert(
                $rows,
                ['id_perguruan_tinggi'],
                ['nama_perguruan_tinggi', 'nama_singkat', 'kode_perguruan_tinggi', 'sync_at']
            );
        });
    }
}

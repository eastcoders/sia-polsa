<?php

namespace App\Jobs;

use App\Models\ProfilePT;
use App\Services\PddiktiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class SyncProfilPTJob implements ShouldQueue
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
        $data = $client->getProfilPT();

        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                ProfilePT::updateOrCreate(
                    [
                        'id_perguruan_tinggi' => trim($row['id_perguruan_tinggi']),
                    ],
                    [
                        'kode_perguruan_tinggi' => trim($row['kode_perguruan_tinggi']),
                        'nama_perguruan_tinggi' => $row['nama_perguruan_tinggi'],
                        'telepon' => $row['telepon'],
                        'email' => $row['email'],
                        'faximile' => trim($row['faximile']),
                        'website' => $row['website'],
                        'jalan' => $row['jalan'],
                        'dusun' => $row['dusun'] ?? '-',
                        'rt_rw' => $row['rt_rw'] ?? '-',
                        'kelurahan' => $row['kelurahan'] ?? '-',
                        'kode_pos' => $row['kode_pos'] ?? '-',
                        'id_wilayah' => $row['id_wilayah'] ?? '-',
                        'sync_at' => now(),
                    ]
                );
            }
        });
    }
}

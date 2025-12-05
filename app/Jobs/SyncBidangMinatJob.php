<?php

namespace App\Jobs;

use App\Models\BidangMinat;
use App\Services\PddiktiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SyncBidangMinatJob implements ShouldQueue
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
        $data = $client->getBidangMinat();

        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                BidangMinat::updateOrCreate(
                    [
                        'id_bidang_minat' => $row['id_bidang_minat'],
                    ],
                    [
                        'nm_bidang_minat' => $row['nm_bidang_minat'],
                        'id_prodi' => $row['id_prodi'],
                        'nama_program_studi' => $row['nama_program_studi'],
                        'smt_mulai' => $row['smt_mulai'],
                        'tamat_sk_bidang_minat' => $row['tamat_sk_bidang_minat'],
                        'sync_at' => now(),
                    ]
                );
            }
        });

        // simpan pesan sukses ke session
        Session::flash('success', 'Sync agama selesai. Total: '.count($data).' data.');
    }
}

<?php

namespace App\Jobs;

use App\Models\AllProdi;
use App\Services\PddiktiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncAllProdiJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    public $tries = 1;

    /**
     * Execute the job.
     */
    // public function handle(PddiktiClient $client): void
    // {
    //     Log::info('SyncAllProdiJob: mulai');

    //     $data = $client->getAllProdi(
    //         ['limit' => 1000]
    //     );

    //     Log::info('SyncAllProdiJob: data diterima', [
    //         'count' => is_countable($data) ? count($data) : null,
    //     ]);

    //     DB::transaction(function () use ($data) {
    //         foreach ($data as $row) {
    //             AllProdi::updateOrCreate(
    //                 [
    //                     'id_prodi' => trim($row['id_prodi']),
    //                 ],
    //                 [
    //                     'id_perguruan_tinggi' => trim($row['id_perguruan_tinggi']),
    //                     'nama_perguruan_tinggi' => trim($row['nama_perguruan_tinggi']),
    //                     'nama_program_studi' => trim($row['nama_program_studi']),
    //                     'kode_program_studi' => $row['kode_program_studi'],
    //                     'status' => $row['status'],
    //                     'id_jenjang_pendidikan' => $row['id_jenjang_pendidikan'],
    //                     'nama_jenjang_pendidikan' => trim($row['nama_jenjang_pendidikan']),
    //                     'sync_at' => now(),
    //                 ]
    //             );
    //         }
    //     });
    // }

    public function handle(PddiktiClient $client): void
    {
        $data = collect($client->getAllProdi());

        // pecah per 1000 data (boleh diubah 500 / 2000 tergantung server)
        $data->chunk(1000)->each(function ($chunk) {
            $rows = $chunk->map(function ($row) {
                return [
                    'id_prodi' => trim($row['id_prodi']),
                    'id_perguruan_tinggi' => trim($row['id_perguruan_tinggi']),
                    'nama_perguruan_tinggi' => trim($row['nama_perguruan_tinggi']),
                    'nama_program_studi' => trim($row['nama_program_studi']),
                    'kode_program_studi' => $row['kode_program_studi'],
                    'status' => $row['status'],
                    'id_jenjang_pendidikan' => $row['id_jenjang_pendidikan'],
                    'nama_jenjang_pendidikan' => trim($row['nama_jenjang_pendidikan']),
                    'sync_at' => now(),
                ];
            })->toArray();

            // mass upsert: 1 query untuk banyak row
            DB::table('all_prodis')->upsert(
                $rows,
                ['id_prodi'], // kolom unik
                [
                    'id_perguruan_tinggi',
                    'nama_perguruan_tinggi',
                    'nama_program_studi',
                    'kode_program_studi',
                    'status',
                    'id_jenjang_pendidikan',
                    'nama_jenjang_pendidikan',
                    'sync_at',
                ]
            );
        });
    }
}

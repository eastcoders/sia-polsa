<?php

namespace App\Jobs;

use App\Models\Semester;
use App\Services\PddiktiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class SyncSemesterJob implements ShouldQueue
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
        $data = $client->getSemester();

        DB::transaction(function () use ($data) {
            foreach ($data as $row) {
                Semester::updateOrCreate(
                    [
                        'id_semester' => trim($row['id_semester']),
                    ],
                    [
                        'nama_semester' => trim($row['nama_semester']),
                        'id_tahun_ajaran' => trim($row['id_tahun_ajaran']),
                        'semester' => $row['semester'],
                        'a_periode_aktif' => $row['a_periode_aktif'],
                        'tanggal_mulai' => trim($row['tanggal_mulai']),
                        'tanggal_selesai' => trim($row['tanggal_selesai']),
                        'sync_at' => now(),
                    ]
                );
            }
        });
    }
}

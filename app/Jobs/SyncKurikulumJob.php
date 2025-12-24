<?php

namespace App\Jobs;

use App\Models\Kurikulum;
use App\Services\PddiktiClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Bus\Batchable;

class SyncKurikulumJob implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $limit,
        public int $offset,
        public array $filter = []
    ) {

    }

    /**
     * Execute the job.
     */
    public function handle(PddiktiClient $client): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        try {
            $data = $client->getKurikulum([
                'filter' => $this->filter['filter'] ?? '',
                'limit' => $this->limit,
                'offset' => $this->offset,
            ]);

            DB::transaction(function () use ($data) {
                foreach ($data as $row) {
                    Kurikulum::updateOrCreate(
                        [
                            'id_server' => $row['id_kurikulum'],
                        ],
                        [
                            'nama_kurikulum' => $row['nama_kurikulum'],
                            'id_prodi' => $row['id_prodi'],
                            'id_semester' => $row['id_semester'],
                            'jumlah_sks_lulus' => $row['jumlah_sks_lulus'] ?? null,
                            'jumlah_sks_wajib' => $row['jumlah_sks_wajib'] ?? null,
                            'jumlah_sks_pilihan' => $row['jumlah_sks_pilihan'] ?? null,
                            'sync_at' => now(),
                            'sync_status' => 'synced',
                            'sync_message' => null,
                        ]
                    );
                }
            });

        } catch (\Exception $e) {
            Log::error("Failed to sync kurikulum offset {$this->offset}: " . $e->getMessage());
            throw $e;
        }

    }
}

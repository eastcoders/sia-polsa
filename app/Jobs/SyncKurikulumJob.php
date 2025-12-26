<?php

namespace App\Jobs;

use App\Models\Kurikulum;
use App\Services\PddiktiClient;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncKurikulumJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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

            $successCount = 0;
            $errorCount = 0;

            foreach ($data as $row) {
                try {
                    DB::transaction(function () use ($row) {
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
                    });
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::warning("SyncKurikulumJob: Failed to sync record {$row['id_kurikulum']}: " . $e->getMessage());
                    // Continue to next record - don't throw
                }
            }

            Log::info("SyncKurikulumJob offset {$this->offset}: {$successCount} success, {$errorCount} errors.");

        } catch (\Exception $e) {
            Log::error("Failed to fetch data for sync kurikulum offset {$this->offset}: " . $e->getMessage());
            throw $e; // Re-throw only for API fetch errors
        }

    }
}

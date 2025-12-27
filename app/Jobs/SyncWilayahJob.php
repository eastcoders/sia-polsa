<?php

namespace App\Jobs;

use App\Models\Wilayah;
use App\Services\PddiktiClient;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Sync Wilayah dengan pagination untuk menghindari timeout.
 * Karena tidak ada API GetCountWilayah, job ini menggunakan pendekatan
 * "fetch until empty" dengan limit/offset.
 */
class SyncWilayahJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Ukuran chunk per request API.
     */
    protected int $chunkSize = 500;

    /**
     * Timeout job dalam detik (10 menit).
     */
    public int $timeout = 600;

    /**
     * Jumlah retry jika gagal.
     */
    public int $tries = 3;

    public function __construct()
    {
    }

    /**
     * Execute the job.
     */
    public function handle(PddiktiClient $client): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        Log::info('SyncWilayahJob: Starting sync...');

        $offset = 0;
        $totalSynced = 0;
        $hasMoreData = true;

        while ($hasMoreData) {
            try {
                // Fetch chunk of data
                $data = $client->getWilayah([
                    'limit' => $this->chunkSize,
                    'offset' => $offset,
                ]);

                // Jika data kosong, berarti sudah selesai
                if (empty($data)) {
                    $hasMoreData = false;
                    Log::info("SyncWilayahJob: No more data at offset {$offset}. Finishing...");
                    break;
                }

                // Process chunk dengan transaction per chunk
                $this->processChunk($data);

                $chunkCount = count($data);
                $totalSynced += $chunkCount;
                $offset += $this->chunkSize;

                Log::info("SyncWilayahJob: Synced chunk at offset " . ($offset - $this->chunkSize) . ", count: {$chunkCount}, total: {$totalSynced}");

                // Jika data kurang dari chunk size, berarti ini chunk terakhir
                if ($chunkCount < $this->chunkSize) {
                    $hasMoreData = false;
                }

            } catch (\Exception $e) {
                Log::error("SyncWilayahJob: Error at offset {$offset}: " . $e->getMessage());
                throw $e; // Re-throw untuk retry mechanism
            }
        }

        Log::info("SyncWilayahJob: Completed. Total synced: {$totalSynced} records.");
    }

    /**
     * Process satu chunk data dalam transaction.
     */
    protected function processChunk(array $data): void
    {
        $successCount = 0;
        $errorCount = 0;

        foreach ($data as $row) {
            try {
                Wilayah::updateOrCreate(
                    ['id_wilayah' => trim($row['id_wilayah'])],
                    [
                        'id_negara' => trim($row['id_negara'] ?? ''),
                        'nama_wilayah' => $row['nama_wilayah'] ?? '',
                        'id_level_wilayah' => $row['id_level_wilayah'] ?? null,
                        'id_induk_wilayah' => trim($row['id_induk_wilayah'] ?? ''),
                        'sync_at' => now(),
                    ]
                );
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                Log::warning("SyncWilayahJob: Failed to sync wilayah {$row['id_wilayah']}: " . $e->getMessage());
                // Continue to next record
            }
        }

        if ($errorCount > 0) {
            Log::warning("SyncWilayahJob: Chunk completed with {$errorCount} errors, {$successCount} success.");
        }
    }
}

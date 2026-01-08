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

                Log::info('SyncWilayahJob: Synced chunk at offset ' . ($offset - $this->chunkSize) . ", count: {$chunkCount}, total: {$totalSynced}");

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
    /**
     * Process satu chunk data dalam transaction.
     */
    protected function processChunk(array $data): void
    {
        $rows = collect($data)->map(function ($row) {
            return [
                'id_wilayah' => trim($row['id_wilayah']),
                'id_negara' => trim($row['id_negara'] ?? ''),
                'nama_wilayah' => $row['nama_wilayah'] ?? '',
                'id_level_wilayah' => $row['id_level_wilayah'] ?? null,
                'id_induk_wilayah' => trim($row['id_induk_wilayah'] ?? ''),
                'sync_at' => now(),
            ];
        })->toArray();

        // Gunakan upsert untuk bulk insert/update
        try {
            \Illuminate\Support\Facades\DB::table('wilayahs')->upsert(
                $rows,
                ['id_wilayah'], // Unique key
                ['id_negara', 'nama_wilayah', 'id_level_wilayah', 'id_induk_wilayah', 'sync_at'] // Update columns
            );
        } catch (\Exception $e) {
            Log::error("SyncWilayahJob: Upsert failed: " . $e->getMessage());
            throw $e;
        }
    }
}

<?php

namespace App\Jobs;

use App\Services\PddiktiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class DispatchSyncMataKuliah implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $filter = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(PddiktiClient $client): void
    {
        try {
            // 1. Get Total Data
            $response = $client->getCountMataKuliah(['filter' => $this->filter['filter'] ?? '']);

            // Handle response format (some endpoints return data directly, some wrapped)
            $totalData = (int) (is_array($response) ? ($response['data'] ?? 0) : $response);

            if ($totalData === 0) {
                Log::info('No mata kuliah data to sync.');

                return;
            }

            // 2. Calculate Batches
            $batchSize = 100; // Chunk Size
            $jobs = [];

            for ($offset = 0; $offset < $totalData; $offset += $batchSize) {
                $jobs[] = new SyncMataKuliahJob($batchSize, $offset, $this->filter);
            }

            // 3. Dispatch Batch
            Bus::batch($jobs)
                ->name('Sync Mata Kuliah ('.$totalData.' records)')
                ->onQueue('default')
                ->allowFailures()
                ->dispatch();

            Log::info("Dispatched batch for {$totalData} mata kuliah records.");

        } catch (\Exception $e) {
            Log::error('Failed to dispatch sync mata kuliah: '.$e->getMessage());
            throw $e;
        }
    }
}

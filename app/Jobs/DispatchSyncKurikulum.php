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

class DispatchSyncKurikulum implements ShouldQueue
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
            $response = $client->getCountKurikulum(['filter' => $this->filter['filter'] ?? '']);

            // Handle response format (some endpoints return data directly, some wrapped)
            $totalData = (int) (is_array($response) ? ($response['data'] ?? 0) : $response);

            if ($totalData === 0) {
                Log::info('No kurikulum data to sync.');

                return;
            }

            // 2. Calculate Batches
            $batchSize = 100; // Chunk Size
            $jobs = [];

            for ($offset = 0; $offset < $totalData; $offset += $batchSize) {
                $jobs[] = new SyncKurikulumJob($batchSize, $offset, $this->filter);
            }

            $filter = $this->filter;

            // 3. Dispatch Batch
            Bus::batch($jobs)
                ->name('Sync Kurikulum ('.$totalData.' records)')
                ->onQueue('default')
                ->allowFailures()
                ->then(function (\Illuminate\Bus\Batch $batch) use ($filter) {
                    Log::info('Dispatching DispatchSyncMatkulKurikulum...');
                    DispatchSyncMatkulKurikulum::dispatch($filter);
                })
                ->dispatch();

            Log::info("Dispatched batch for {$totalData} kurikulum records.");

        } catch (\Exception $e) {
            Log::error('Failed to dispatch sync kurikulum: '.$e->getMessage());
            throw $e;
        }
    }
}

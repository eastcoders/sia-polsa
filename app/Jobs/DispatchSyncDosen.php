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

class DispatchSyncDosen implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $filter = []
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(PddiktiClient $client): void
    {
        try {
            // 1. Get Total Data
            $response = $client->getCountDosen(['filter' => $this->filter['filter'] ?? '']);

            // Handle response format
            $totalData = (int) (is_array($response) ? ($response['data'] ?? 0) : $response);
            $batchSize = 300; // Chunk Size
            $filter = $this->filter;

            if ($totalData === 0) {
                Log::info('Total data is 0, attempting recursive sync for Dosen.');

                Bus::batch([
                    new SyncDosenJob($batchSize, 0, $this->filter, true),
                ])
                    ->name('Sync Dosen (Recursive)')
                    ->onQueue('default')
                    ->allowFailures()
                    ->finally(function (\Illuminate\Bus\Batch $batch) use ($filter) {
                        Log::info('Dispatching DispatchSyncPenugasanDosen from recursive batch...');
                        DispatchSyncPenugasanDosen::dispatch($filter);
                    })
                    ->dispatch();

                return;
            }

            // 2. Calculate Batches
            $jobs = [];

            for ($offset = 0; $offset < $totalData; $offset += $batchSize) {
                $jobs[] = new SyncDosenJob($batchSize, $offset, $this->filter, false);
            }

            // 3. Dispatch Batch
            Bus::batch($jobs)
                ->name('Sync Dosen (' . $totalData . ' records)')
                ->onQueue('default')
                ->allowFailures()
                ->finally(function (\Illuminate\Bus\Batch $batch) use ($filter) {
                    Log::info('Dispatching DispatchSyncPenugasanDosen...');
                    DispatchSyncPenugasanDosen::dispatch($filter);
                })
                ->dispatch();

            Log::info("Dispatched batch for {$totalData} dosen records.");

        } catch (\Exception $e) {
            Log::error('Failed to dispatch sync dosen: ' . $e->getMessage());
            throw $e;
        }
    }
}

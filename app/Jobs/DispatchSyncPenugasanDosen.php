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

class DispatchSyncPenugasanDosen implements ShouldQueue
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
            // Use GetCountPenugasanSemuaDosen as requested
            $response = $client->getCountPenugasanSemuaDosen(['filter' => $this->filter['filter'] ?? '']);

            // Handle response format
            $totalData = (int) (is_array($response) ? ($response['data'] ?? 0) : $response);

            if ($totalData === 0) {
                Log::info('Total data is 0, attempting recursive sync for Penugasan Dosen.');

                $batchSize = 300;
                Bus::batch([
                    new SyncPenugasanDosenJob($batchSize, 0, $this->filter, true),
                ])
                    ->name('Sync Penugasan Dosen (Recursive)')
                    ->onQueue('default')
                    ->allowFailures()
                    ->dispatch();

                return;
            }

            // 2. Calculate Batches
            $batchSize = 300; // Chunk Size
            $jobs = [];

            for ($offset = 0; $offset < $totalData; $offset += $batchSize) {
                $jobs[] = new SyncPenugasanDosenJob($batchSize, $offset, $this->filter, false);
            }

            // 3. Dispatch Batch
            Bus::batch($jobs)
                ->name('Sync Penugasan Dosen ('.$totalData.' records)')
                ->onQueue('default')
                ->allowFailures()
                ->dispatch();

            Log::info("Dispatched batch for {$totalData} penugasan dosen records.");

        } catch (\Exception $e) {
            Log::error('Failed to dispatch sync penugasan dosen: '.$e->getMessage());
            throw $e;
        }
    }
}

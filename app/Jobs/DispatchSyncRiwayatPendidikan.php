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

class DispatchSyncRiwayatPendidikan implements ShouldQueue
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
            $response = $client->getCountRiwayatPendidikanMahasiswa(['filter' => $this->filter['filter'] ?? '']);

            $totalData = (int) ($response ?? 0);

            if ($totalData === 0) {
                Log::info('No riwayat pendidikan data to sync.');

                return;
            }

            // 2. Calculate Batches
            $batchSize = 100;
            $jobs = [];

            for ($offset = 0; $offset < $totalData; $offset += $batchSize) {
                $jobs[] = new SyncRiwayatPendidikanPageJob($batchSize, $offset, $this->filter);
            }

            // 3. Dispatch Batch
            $startTime = now();
            $filter = $this->filter;

            Bus::batch($jobs)
                ->name('Sync Riwayat Pendidikan ('.$totalData.' records)')
                ->onQueue('default')
                ->allowFailures()
                ->then(function (\Illuminate\Bus\Batch $batch) use ($startTime, $filter) {
                    Log::info('Riwayat Pendidikan Batch Finished. Dispatching CleanupSyncRiwayatPendidikanJob...');
                    CleanupSyncRiwayatPendidikanJob::dispatch($startTime, $filter);
                })
                ->dispatch();

            Log::info("Dispatched batch for {$totalData} riwayat pendidikan records.");

        } catch (\Exception $e) {
            Log::error('Failed to dispatch sync riwayat pendidikan: '.$e->getMessage());
            throw $e;
        }
    }
}

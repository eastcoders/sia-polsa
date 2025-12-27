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

class DispatchSyncMahasiswa implements ShouldQueue
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
            $response = $client->getCountMahasiswa(['filter' => $this->filter['filter'] ?? '']);
            // Response format: {"error_code": "0", "data": "3867"}

            // dd($response);

            $totalData = (int) ($response ?? 0);

            if ($totalData === 0) {
                Log::info('No mahasiswa data to sync.');
                return;
            }

            // 2. Calculate Batches
            $batchSize = 100; // Chunk Size
            $jobs = [];

            for ($offset = 0; $offset < $totalData; $offset += $batchSize) {
                $jobs[] = new SyncMahasiswaPageJob($batchSize, $offset, $this->filter);
            }

            // 3. Dispatch Batch
            $startTime = now();
            $filter = $this->filter;

            Bus::batch($jobs)
                ->name('Sync Mahasiswa (' . $totalData . ' records)')
                ->onQueue('default')
                ->allowFailures()
                ->then(function (\Illuminate\Bus\Batch $batch) use ($startTime, $filter) {
                    Log::info('Batch Finished. Dispatching CleanupSyncMahasiswaJob...');
                    CleanupSyncMahasiswaJob::dispatch($startTime, $filter);

                    // Lanjutkan ke sync riwayat pendidikan
                    Log::info('Dispatching DispatchSyncRiwayatPendidikan...');
                    DispatchSyncRiwayatPendidikan::dispatch($filter);
                })
                ->dispatch();

            Log::info("Dispatched batch for {$totalData} mahasiswa records.");

        } catch (\Exception $e) {
            Log::error("Failed to dispatch sync mahasiswa: " . $e->getMessage());
            throw $e;
        }
    }
}

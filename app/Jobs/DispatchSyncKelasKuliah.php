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

class DispatchSyncKelasKuliah implements ShouldQueue
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
            $batchSize = 300; // Chunk Size
            // $response = $client->getCountKelasKuliah(['filter' => $this->filter['filter'] ?? '']);
            $response = 0;

            // Handle response format (some endpoints return data directly, some wrapped)
            $totalData = (int) (is_array($response) ? ($response['data'] ?? 0) : $response);

            $filter = $this->filter;

            if ($totalData === 0) {
                Log::info('Total data is 0, attempting recursive sync for Kelas Kuliah.');

                Bus::batch([
                    new SyncKelasKuliahJob($batchSize, 0, $this->filter, true),
                ])
                    ->name('Sync Kelas Kuliah (Recursive)')
                    ->onQueue('default')
                    ->allowFailures()
                    ->finally(function (\Illuminate\Bus\Batch $batch) use ($filter) {
                        Log::info('Dispatching DispatchSyncDosenPengajarKelasKuliah from recursive batch...');
                        DispatchSyncDosenPengajarKelasKuliah::dispatch($filter);

                    })
                    ->dispatch();

                return;
            }

            // 2. Calculate Batches
            $jobs = [];

            for ($offset = 0; $offset < $totalData; $offset += $batchSize) {
                // False for recursive param because we know the total
                $jobs[] = new SyncKelasKuliahJob($batchSize, $offset, $this->filter, false);
            }

            // 3. Dispatch Batch
            Bus::batch($jobs)
                ->name('Sync Kelas Kuliah ('.$totalData.' records)')
                ->onQueue('default')
                ->allowFailures()
                ->finally(function (\Illuminate\Bus\Batch $batch) use ($filter) {
                    Log::info('Dispatching DispatchSyncDosenPengajarKelasKuliah...');
                    DispatchSyncDosenPengajarKelasKuliah::dispatch($filter);

                })
                ->dispatch();

            Log::info("Dispatched batch for {$totalData} kelas kuliah records.");

        } catch (\Exception $e) {
            Log::error('Failed to dispatch sync kelas kuliah: '.$e->getMessage());
            throw $e;
        }
    }
}

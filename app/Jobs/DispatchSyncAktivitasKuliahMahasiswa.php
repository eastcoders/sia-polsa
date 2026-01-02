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

class DispatchSyncAktivitasKuliahMahasiswa implements ShouldQueue
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
            $response = $client->getCountAktivitasKuliahMahasiswa(['filter' => $this->filter['filter'] ?? '']);

            $totalData = (int) ($response ?? 0);

            if ($totalData === 0) {
                Log::info('No aktivitas kuliah mahasiswa data to sync.');

                return;
            }

            // 2. Calculate Batches
            $batchSize = 300; // Chunk Size
            $jobs = [];

            for ($offset = 0; $offset < $totalData; $offset += $batchSize) {
                $jobs[] = new SyncAktivitasKuliahMahasiswaBatch($batchSize, $offset, $this->filter);
            }

            // 3. Dispatch Batch
            Bus::batch($jobs)
                ->name('Sync Aktivitas Kuliah Mahasiswa (' . $totalData . ' records)')
                ->onQueue('default')
                ->allowFailures()
                ->dispatch();

            Log::info("Dispatched batch for {$totalData} aktivitas kuliah mahasiswa records.");

        } catch (\Exception $e) {
            Log::error('Failed to dispatch sync aktivitas kuliah mahasiswa: ' . $e->getMessage());
            throw $e;
        }
    }
}

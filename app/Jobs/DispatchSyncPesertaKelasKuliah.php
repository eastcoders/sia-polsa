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

class DispatchSyncPesertaKelasKuliah implements ShouldQueue
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
            $response = $client->getCountPesertaKelasKuliah(['filter' => $this->filter['filter'] ?? '']);

            $totalData = (int) ($response ?? 0);

            if ($totalData === 0) {
                Log::info('No matkul kurikulum data to sync.');

                return;
            }

            // 2. Calculate Batches
            $batchSize = 300;
            $jobs = [];

            for ($offset = 0; $offset < $totalData; $offset += $batchSize) {
                $jobs[] = new SyncPesertaKelasKuliahPageJob($batchSize, $offset, $this->filter);
            }

            Bus::batch($jobs)
                ->name('Sync Peserta Kelas Kuliah(' . $totalData . ' records)')
                ->onQueue('default')
                ->allowFailures()
                ->dispatch();

            Log::info("Dispatched batch for {$totalData} peserta kelas kuliah records.");

        } catch (\Exception $e) {
            Log::error('Failed to dispatch sync peserta kelas kuliah: ' . $e->getMessage());
            throw $e;
        }
    }
}

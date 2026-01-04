<?php

namespace App\Jobs;

use App\Models\PesertaKelasKuliah;
use App\Services\PddiktiClient;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncPesertaKelasKuliahPageJob implements ShouldQueue
{
    use Batchable, Dispatchable, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $limit,
        public int $offset,
        public array $filter = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(PddiktiClient $client): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        try {
            $data = $client->getPesertaKelasKuliah([
                'filter' => $this->filter['filter'] ?? '',
                'limit' => $this->limit,
                'offset' => $this->offset,
            ]);

            $successCount = 0;
            $errorCount = 0;

            foreach ($data as $row) {
                try {
                    DB::transaction(function () use ($row) {
                        // Cek apakah record sudah ada
                        PesertaKelasKuliah::updateOrCreate(
                            [
                                'id_kelas_kuliah' => $row['id_kelas_kuliah'],
                                'id_registrasi_mahasiswa' => $row['id_registrasi_mahasiswa'],
                            ], [
                                'sync_at' => now(),
                                'sync_status' => 'synced',
                                'sync_message' => null,
                            ]
                        );
                    });
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::warning("SyncPesertaKelasKuliahPageJob: Failed to sync record {$row['id_registrasi_mahasiswa']}: ".$e->getMessage());
                    // Continue to next record - don't throw
                }
            }

            Log::info("SyncPesertaKelasKuliahPageJob offset {$this->offset}: {$successCount} success, {$errorCount} errors.");

        } catch (\Exception $e) {
            Log::error("Failed to fetch data for sync peserta kelas kuliah page offset {$this->offset}: ".$e->getMessage());
            throw $e; // Re-throw only for API fetch errors
        }
    }
}

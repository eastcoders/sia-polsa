<?php

namespace App\Jobs;

use App\Models\MatkulKurikulum;
use App\Services\PddiktiClient;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncMatkulKurikulumPageJob implements ShouldQueue
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
            $data = $client->getMatkulKurikulum([
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
                        $existing = MatkulKurikulum::where('id_kurikulum', $row['id_kurikulum'])
                            ->where('id_matkul', $row['id_matkul'])
                            ->first();

                        $updateData = [
                            'semester' => $row['semester'] ?? null,
                            'apakah_wajib' => $row['apakah_wajib'] ?? null,
                            'sync_at' => now(),
                            'sync_status' => 'synced',
                            'sync_message' => null,
                        ];

                        // Strategi ID aman: Hanya set ID jika record baru atau kolom kosong
                        if (! $existing || empty($existing->id_kurikulum) || empty($existing->id_matkul)) {
                            $updateData['id_kurikulum'] = $row['id_kurikulum'];
                        }

                        MatkulKurikulum::updateOrCreate(
                            [
                                'id_kurikulum' => $row['id_kurikulum'],
                                'id_matkul' => $row['id_matkul'],
                            ],
                            $updateData
                        );
                    });
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::warning("SyncMatkukKurikulumPageJob: Failed to sync record {$row['id_registrasi_mahasiswa']}: ".$e->getMessage());
                    // Continue to next record - don't throw
                }
            }

            Log::info("SyncMatkukKurikulumPageJob offset {$this->offset}: {$successCount} success, {$errorCount} errors.");

        } catch (\Exception $e) {
            Log::error("Failed to fetch data for sync matkul kurikulum page offset {$this->offset}: ".$e->getMessage());
            throw $e; // Re-throw only for API fetch errors
        }
    }
}

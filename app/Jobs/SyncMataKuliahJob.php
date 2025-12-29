<?php

namespace App\Jobs;

use App\Models\MataKuliah;
use App\Services\PddiktiClient;
use Carbon\Carbon;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncMataKuliahJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
            $data = $client->getMataKuliah([
                'filter' => $this->filter['filter'] ?? '',
                'limit' => $this->limit,
                'offset' => $this->offset,
            ]);

            $successCount = 0;
            $errorCount = 0;

            foreach ($data as $row) {
                try {
                    DB::transaction(function () use ($row) {

                        $existing = MataKuliah::where('id_server', $row['id_matkul'])->first();

                        $updateData =
                            [
                                'nama_mata_kuliah' => $row['nama_mata_kuliah'],
                                'kode_mata_kuliah' => $row['kode_mata_kuliah'],
                                'sks_mata_kuliah' => $row['sks_mata_kuliah'] ?? '0',
                                'sks_tatap_muka' => $row['sks_tatap_muka'] ?? '0',
                                'sks_praktek' => $row['sks_praktek'] ?? '0',
                                'sks_praktek_lapangan' => $row['sks_praktek_lapangan'] ?? '0',
                                'sks_simulasi' => $row['sks_simulasi'] ?? '0',
                                'id_prodi' => $row['id_prodi'],
                                'id_jenis_mata_kuliah' => $row['id_jenis_mata_kuliah'] ?? 'A',
                                'id_kelompok_mata_kuliah' => $row['id_kelompok_mata_kuliah'],
                                'metode_kuliah' => $row['metode_kuliah'],
                                'tanggal_mulai_efektif' => isset($row['tanggal_mulai_efektif'])
                                    ? Carbon::parse($row['tanggal_mulai_efektif'])->format('Y-m-d')
                                    : null,
                                'tanggal_akhir_efektif' => isset($row['tanggal_akhir_efektif'])
                                    ? Carbon::parse($row['tanggal_selesai_efektif'])->format('Y-m-d')
                                    : null,
                                'sync_at' => now(),
                                'sync_status' => 'synced',
                                'sync_message' => null,
                            ];

                        if (! $existing || empty($existing->id_matkul)) {
                            $updateData['id_matkul'] = $row['id_matkul'];
                        }

                        MataKuliah::updateOrCreate([
                            'id_server' => $row['id_matkul'],
                        ], $updateData);
                    });
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::warning("SyncMataKuliahJob: Failed to sync record {$row['id_matkul']}: ".$e->getMessage());
                    // Continue to next record - don't throw
                }
            }

            Log::info("SyncMataKuliahJob offset {$this->offset}: {$successCount} success, {$errorCount} errors.");

        } catch (\Exception $e) {
            Log::error("Failed to fetch data for sync mata kuliah offset {$this->offset}: ".$e->getMessage());
            throw $e; // Re-throw only for API fetch errors
        }
    }
}

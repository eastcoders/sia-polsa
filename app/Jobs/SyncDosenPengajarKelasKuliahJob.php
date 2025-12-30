<?php

namespace App\Jobs;

use App\Models\DosenPengajarKelasKuliah;
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

class SyncDosenPengajarKelasKuliahJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $limit,
        public int $offset,
        public array $filter = [],
        public bool $recursive = false,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(PddiktiClient $client): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        try {
            $data = $client->getDosenPengajarKelasKuliah([
                'filter' => $this->filter['filter'] ?? '',
                'limit' => $this->limit,
                'offset' => $this->offset,
            ]);

            $successCount = 0;
            $errorCount = 0;

            foreach ($data as $row) {
                try {
                    DB::transaction(function () use ($row) {

                        $existing = DosenPengajarKelasKuliah::where('id_server', $row['id_aktivitas_mengajar'])->first();

                        $updateData = [
                            'id_registrasi_dosen' => $row['id_registrasi_dosen'],
                            'id_kelas_kuliah' => $row['id_kelas_kuliah'],
                            'id_substansi' => $row['id_substansi'],
                            'sks_substansi_total' => $row['sks_substansi_total'] ?? null,
                            'rencana_minggu_pertemuan' => $row['rencana_minggu_pertemuan'] ?? 0,
                            'realisasi_minggu_pertemuan' => $row['realisasi_minggu_pertemuan'] ?? 0,
                            'id_jenis_evaluasi' => $row['id_jenis_evaluasi'] ?? 0,
                            'sync_at' => now(),
                            'sync_status' => 'synced',
                            'sync_message' => null,
                        ];

                        if (!$existing || empty($exiting->id_aktivitas_mengajar)) {
                            $updateData['id_aktivitas_mengajar'] = $row['id_aktivitas_mengajar'];
                        }

                        DosenPengajarKelasKuliah::updateOrCreate(
                            [
                                'id_server' => $row['id_aktivitas_mengajar'],
                            ],
                            $updateData
                        );
                    });
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::warning("SyncDosenPengajarKelasKuliahJob: Failed to sync record {$row['id_aktivitas_mengajar']}: " . $e->getMessage());
                    // Continue to next record - don't throw
                }
            }

            Log::info("SyncDosenPengajarKelasKuliahJob offset {$this->offset}: {$successCount} success, {$errorCount} errors.");

            // Handle Recursive Logic
            if ($this->recursive && count($data) >= $this->limit) {
                $this->batch()->add([
                    new SyncDosenPengajarKelasKuliahJob(
                        $this->limit,
                        $this->offset + $this->limit,
                        $this->filter,
                        true
                    ),
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Failed to fetch data for sync dosen pengajar kelas kuliah offset {$this->offset}: " . $e->getMessage());
            throw $e; // Re-throw only for API fetch errors
        }

    }
}

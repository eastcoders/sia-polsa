<?php

namespace App\Jobs;

use App\Models\KelasKuliah;
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

class SyncKelasKuliahJob implements ShouldQueue
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
            $data = $client->getDetailKelasKuliah([
                'filter' => $this->filter['filter'] ?? '',
                'limit' => $this->limit,
                'offset' => $this->offset,
            ]);

            $successCount = 0;
            $errorCount = 0;

            foreach ($data as $row) {
                try {
                    DB::transaction(function () use ($row) {

                        $existing = KelasKuliah::where('id_server', $row['id_kelas_kuliah'])->first();

                        $updateData = [
                            'nama_kelas_kuliah' => $row['nama_kelas_kuliah'],
                            'id_prodi' => $row['id_prodi'],
                            'id_semester' => $row['id_semester'],
                            'id_matkul' => $row['id_matkul'] ?? null,
                            'sks_mk' => $row['sks_mk'] ?? 0,
                            'sks_tm' => $row['sks_tm'] ?? 0,
                            'sks_prak' => $row['sks_prak'] ?? 0,
                            'sks_sim' => $row['sks_sim'] ?? 0,
                            'bahasan' => $row['bahasan'] ?? null,
                            'tanggal_mulai_efektif' => isset($row['tanggal_mulai_efektif'])
                                ? Carbon::parse($row['tanggal_mulai_efektif'])->format('Y-m-d')
                                : null,
                            'tanggal_akhir_efektif' => isset($row['tanggal_akhir_efektif'])
                                ? Carbon::parse($row['tanggal_akhir_efektif'])->format('Y-m-d')
                                : null,
                            'sync_at' => now(),
                            'sync_status' => 'synced',
                            'sync_message' => null,
                        ];

                        if (! $existing || empty($exiting->id_kelas_kuliah)) {
                            $updateData['id_kelas_kuliah'] = $row['id_kelas_kuliah'];
                        }

                        KelasKuliah::updateOrCreate(
                            [
                                'id_server' => $row['id_kelas_kuliah'],
                            ],
                            $updateData
                        );
                    });
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::warning("SyncKelasKuliahJob: Failed to sync record {$row['id_kelas_kuliah']}: ".$e->getMessage());
                    // Continue to next record - don't throw
                }
            }

            Log::info("SyncKelasKuliahJob offset {$this->offset}: {$successCount} success, {$errorCount} errors.");

            // Handle Recursive Logic
            if ($this->recursive && count($data) >= $this->limit) {
                $this->batch()->add([
                    new SyncKelasKuliahJob(
                        $this->limit,
                        $this->offset + $this->limit,
                        $this->filter,
                        true
                    ),
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Failed to fetch data for sync kelas kuliah offset {$this->offset}: ".$e->getMessage());
            throw $e; // Re-throw only for API fetch errors
        }

    }
}

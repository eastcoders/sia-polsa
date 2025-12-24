<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Kurikulum;
use App\Models\KelasKuliah;
use Illuminate\Bus\Batchable;
use App\Services\PddiktiClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncKelasKuliahJob implements ShouldQueue
{
    use Batchable, Queueable;

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
            $data = $client->getDetailKelasKuliah([
                'filter' => $this->filter['filter'] ?? '',
                'limit' => $this->limit,
                'offset' => $this->offset,
            ]);

            DB::transaction(function () use ($data, $client) {

                foreach ($data as $row) {

                    KelasKuliah::updateOrCreate(
                        [
                            'id_server' => $row['id_kelas_kuliah'],
                        ],
                        [
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
                        ]
                    );

                }

            });

            // Handle Recursive Logic
            if ($this->recursive && count($data) >= $this->limit) {
                $this->batch()->add([
                    new SyncKelasKuliahJob(
                        $this->limit,
                        $this->offset + $this->limit,
                        $this->filter,
                        true
                    )
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Failed to sync kelas kuliah offset {$this->offset}: " . $e->getMessage());
            throw $e;
        }

    }
}

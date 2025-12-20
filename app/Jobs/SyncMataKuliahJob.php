<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\MataKuliah;
use Illuminate\Bus\Batchable;
use App\Services\PddiktiClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncMataKuliahJob implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $limit,
        public int $offset,
        public array $filter = []
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
            $data = $client->getMataKuliah([
                'filter' => $this->filter['filter'] ?? '',
                'limit' => $this->limit,
                'offset' => $this->offset,
            ]);

            DB::transaction(function () use ($data) {
                foreach ($data as $row) {
                    MataKuliah::updateOrCreate([
                        'id_server' => $row['id_matkul'],
                    ], [
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
                    ]);
                }
            });
        } catch (\Exception $e) {
            Log::error("Failed to sync page offset {$this->offset}: " . $e->getMessage());
            throw $e; // Re-throw to mark batch as failure
        }
    }
}

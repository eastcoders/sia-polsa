<?php

namespace App\Jobs;

use App\Models\MataKuliah;
use App\Services\PddiktiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PushMataKuliahJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public MataKuliah $mata_kuliah
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(PddiktiClient $client): void
    {
        try {

            $data = [
                'id_matkul' => $this->mata_kuliah->id_matkul,
                'kode_mata_kuliah' => $this->mata_kuliah->kode_mata_kuliah,
                'nama_mata_kuliah' => $this->mata_kuliah->nama_mata_kuliah,
                'sks_mata_kuliah' => $this->mata_kuliah->sks_mata_kuliah,
                'sks_tatap_muka' => $this->mata_kuliah->sks_tatap_muka,
                'sks_simulasi' => $this->mata_kuliah->sks_simulasi,
                'sks_praktek' => $this->mata_kuliah->sks_praktek,
                'sks_praktek_lapangan' => $this->mata_kuliah->sks_praktek_lapangan,
                'id_prodi' => $this->mata_kuliah->id_prodi,
                'id_jenis_mata_kuliah' => $this->mata_kuliah->id_jenis_mata_kuliah,
                'id_kelompok_mata_kuliah' => $this->mata_kuliah->id_kelompok_mata_kuliah,
                'metode_kuliah' => $this->mata_kuliah->metode_kuliah,
                'tanggal_mulai_efektif' => $this->mata_kuliah->tanggal_mulai_efektif,
                'tanggal_akhir_efektif' => $this->mata_kuliah->tanggal_akhir_efektif,
            ];

            if (empty($this->mata_kuliah->id_server)) {
                $response = $client->insertMataKuliah($data);
                $idServer = $response['id_matkul'] ?? $response['id'] ?? null;

                if (!$idServer) {
                    throw new \Exception("Gagal mendapatkan ID Server setelah insert Mata Kuliah.");
                }

                $this->mata_kuliah->update([
                    'id_server' => $idServer,
                    'sync_status' => 'synced', // Temporary, wait for Riwayat
                    'sync_message' => null,
                    'sync_at' => now(),
                ]);
            } else {
                $idServer = $this->mata_kuliah->id_server;
            }

        } catch (\Exception $e) {
            Log::error("Failed to push mata kuliah {$this->mata_kuliah->id}: " . $e->getMessage());

            $this->mata_kuliah->update([
                'sync_status' => 'failed',
                'sync_message' => substr($e->getMessage(), 0, 255),
            ]);

            throw $e;
        }

    }
}

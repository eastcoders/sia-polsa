<?php

namespace App\Jobs;

use App\Models\Kurikulum;
use App\Services\PddiktiClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class PushKurikulumJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Kurikulum $kurikulum
    ) {

        $this->kurikulum->loadMissing('matkulKurikulum.matkul');
    }

    /**
     * Execute the job.
     */
    public function handle(PddiktiClient $client): void
    {
        try {

            $data = [
                'nama_kurikulum' => $this->kurikulum->nama_kurikulum,
                'id_prodi' => $this->kurikulum->id_prodi,
                'id_semester' => $this->kurikulum->id_semester,
                'jumlah_sks_lulus' => $this->kurikulum->jumlah_sks_lulus,
                'jumlah_sks_wajib' => $this->kurikulum->jumlah_sks_wajib,
                'jumlah_sks_pilihan' => $this->kurikulum->jumlah_sks_pilihan,
            ];

            if (empty($this->kurikulum->id_server)) {
                $response = $client->insertKurikulum($data);
                $idServer = $response['id_kurikulum'] ?? $response['id'] ?? null;

                if (!$idServer) {
                    throw new \Exception("Gagal mendapatkan ID Server setelah insert Kurikulum.");
                }

                $this->kurikulum->update([
                    'id_server' => $idServer,
                    'sync_status' => 'synced',
                    'sync_message' => null,
                    'sync_at' => now(),
                ]);
            } else {
                $idServer = $this->kurikulum->id_server;
            }

            // ... setelah bagian $idServer = ...

            // Ambil semua matkul kurikulum terkait
            $matkulKurikulums = $this->kurikulum->matkulKurikulum;

            if ($matkulKurikulums->isEmpty()) {
                Log::info("Tidak ada mata kuliah untuk kurikulum {$this->kurikulum->id}");
                return; // atau lanjutkan logika sesuai kebutuhan
            }

            foreach ($matkulKurikulums as $mk) {
                // Pastikan relasi `matkul` dimuat
                if (!$mk->relationLoaded('matkul') || !$mk->matkul) {
                    Log::warning("Mata kuliah tidak ditemukan untuk matkul_kurikulum ID {$mk->id}");
                    continue;
                }

                $data_matkul_kurikulum = [
                    'id_kurikulum' => $idServer,
                    'id_matkul' => $mk->matkul->id_server,
                    'semester' => $mk->semester,
                    'sks_mata_kuliah' => $mk->sks_mata_kuliah,
                    'sks_tatap_muka' => $mk->sks_tatap_muka,
                    'sks_praktek' => $mk->sks_praktek,
                    'sks_praktek_lapangan' => $mk->sks_praktek_lapangan,
                    'sks_praktek_simulasi' => $mk->sks_praktek_simulasi,
                    'apakah_wajib' => $mk->apakah_wajib,
                ];

                // Kirim ke PDDIKTI
                $response = $client->insertMatkulKurikulum($data_matkul_kurikulum);

                // Opsional: simpan respons atau log
                Log::info("Berhasil push matkul kurikulum: " . ($mk->matkul->kode ?? 'N/A'));
            }

        } catch (\Exception $e) {
            Log::error("Failed to push kurikasdaulum {$this->kurikulum->id}: " . $e->getMessage());

            $this->kurikulum->update([
                'sync_status' => 'failed',
                'sync_message' => substr($e->getMessage(), 0, 255),
            ]);

            throw $e;
        }

    }
}

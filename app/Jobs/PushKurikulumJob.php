<?php

namespace App\Jobs;

use App\Models\Kurikulum;
use App\Services\PddiktiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PushKurikulumJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Kurikulum $kurikulum
    ) {}

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

                if (! $idServer) {
                    throw new \Exception('Gagal mendapatkan ID Server setelah insert Kurikulum.');
                }

                $this->kurikulum->update([
                    'id_server' => $idServer,
                    'sync_status' => 'synced',
                    'sync_message' => null,
                    'sync_at' => now(),
                ]);
            } else {

                $key = ['id_kurikulum' => $this->kurikulum->id_server];
                $response = $client->updateKurikulum($key, $data);
                $idServer = $response['id_kurikulum'] ?? $response['id'] ?? null;

                $this->kurikulum->update([
                    'id_server' => $idServer,
                    'sync_status' => 'synced',
                    'sync_message' => null,
                    'sync_at' => now(),
                ]);
            }

            // Ambil semua matkul kurikulum terkait
            // PENTING: Load di sini karena SerializesModels akan me-reload model "fresh" di worker,
            // sehingga loadMissing di constructor akan hilang.
            $this->kurikulum->load([
                'matkulKurikulum' => fn ($query) => $query->withTrashed(),
                'matkulKurikulum.matkul',
            ]);

            $matkulKurikulums = $this->kurikulum->matkulKurikulum;

            if ($matkulKurikulums->isEmpty()) {
                Log::info("Tidak ada mata kuliah untuk kurikulum {$this->kurikulum->nama_kurikulum}");

                return;
            }

            foreach ($matkulKurikulums as $mk) {
                // Pastikan relasi `matkul` dimuat
                if (! $mk->relationLoaded('matkul') || ! $mk->matkul) {
                    Log::warning("Mata kuliah tidak ditemukan untuk matkul_kurikulum ID {$mk->id}");

                    continue;
                }

                // Jika record soft-deleted, lakukan DELETE ke server
                if ($mk->trashed()) {
                    try {
                        $client->deleteMatkulKurikulum([
                            'id_kurikulum' => $idServer,
                            'id_matkul' => $mk->matkul->id_server,
                        ]);

                        Log::info('Berhasil delete matkul kurikulum: '.($mk->matkul->kode_mata_kuliah ?? 'N/A'));

                        // Opsional: update status sync di local
                        $mk->update([
                            'sync_status' => 'synced',
                            'sync_message' => 'Deleted from server',
                        ]);

                    } catch (\Exception $e) {
                        $mk->update([
                            'sync_status' => 'failed',
                            'sync_message' => "Gagal delete matkul kurikulum (ID MK Local: {$mk->id}): ".$e->getMessage(),
                        ]);
                        Log::error("Gagal delete matkul kurikulum (ID MK Local: {$mk->id}): ".$e->getMessage());
                    }

                    continue; // Skip insert logic
                }

                // Jika record aktif, lakukan INSERT ke server
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

                try {
                    $response = $client->insertMatkulKurikulum($data_matkul_kurikulum);
                    Log::info('Berhasil push matkul kurikulum: '.($mk->matkul->kode ?? 'N/A'));

                    $mk->update([
                        'sync_status' => 'synced',
                        'sync_message' => null,
                    ]);

                } catch (\Exception $e) {
                    $mk->update([
                        'sync_status' => 'failed',
                        'sync_message' => "Gagal push matkul kurikulum (ID MK Local: {$mk->id}): ".$e->getMessage(),
                    ]);
                    Log::error("Gagal push matkul kurikulum (ID MK Local: {$mk->id}): ".$e->getMessage());
                }
            }

        } catch (\Exception $e) {
            Log::error("Failed to push kurikulum {$this->kurikulum->id}: ".$e->getMessage());

            $this->kurikulum->update([
                'sync_status' => 'failed',
                'sync_message' => substr($e->getMessage(), 0, 255),
            ]);

            throw $e;
        }

    }
}

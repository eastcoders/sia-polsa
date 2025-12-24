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

        } catch (\Exception $e) {
            Log::error("Failed to push kurikulum {$this->kurikulum->id}: " . $e->getMessage());

            $this->kurikulum->update([
                'sync_status' => 'failed',
                'sync_message' => substr($e->getMessage(), 0, 255),
            ]);

            throw $e;
        }

    }
}

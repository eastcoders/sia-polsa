<?php

namespace App\Jobs;

use App\Models\KelasKuliah;
use App\Services\PddiktiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PushKelasKuliahJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public KelasKuliah $kelas_kuliah
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(PddiktiClient $client): void
    {
        try {

            $data = [
                'id_prodi' => $this->kelas_kuliah->id_prodi,
                'id_semester' => $this->kelas_kuliah->id_semester,
                'nama_kelas_kuliah' => $this->kelas_kuliah->nama_kelas_kuliah,
                'sks_mk' => $this->kelas_kuliah->sks_mk,
                'sks_tm' => $this->kelas_kuliah->sks_tm,
                'sks_sim' => $this->kelas_kuliah->sks_sim,
                'sks_prak' => $this->kelas_kuliah->sks_prak,
                'sks_prak_lap' => $this->kelas_kuliah->sks_prak_lap,
                'bahasan' => $this->kelas_kuliah->bahasan,
                'a_selenggara_pditt' => $this->kelas_kuliah->a_selenggara_pditt,
                'apa_untuk_pditt' => $this->kelas_kuliah->apa_untuk_pditt,
                'kapasitas' => $this->kelas_kuliah->kapasitas,
                'tanggal_mulai_efektif' => $this->kelas_kuliah->tanggal_mulai_efektif,
                'tanggal_akhir_efektif' => $this->kelas_kuliah->tanggal_akhir_efektif,
                'id_mou' => $this->kelas_kuliah->id_mou,
                'id_matkul' => $this->kelas_kuliah->matkul->id_server,
                'lingkup' => $this->kelas_kuliah->lingkup,
                'mode' => $this->kelas_kuliah->mode
            ];

            // dd($data['id_matkul']);



            if (empty($this->kelas_kuliah->id_server)) {
                $response = $client->insertKelasKuliah($data);
                $idServer = $response['id_kelas_kuliah'] ?? $response['id'] ?? null;

                if (!$idServer) {
                    throw new \Exception("Gagal mendapatkan ID Server setelah insert Mata Kuliah.");
                }

                $this->kelas_kuliah->update([
                    'id_server' => $idServer,
                    'sync_status' => 'synced',
                    'sync_message' => null,
                    'sync_at' => now(),
                ]);
            } else {
                $idServer = $this->kelas_kuliah->id_server;
            }

        } catch (\Exception $e) {
            Log::error("Failed to push kelas kuliah {$this->kelas_kuliah->id}: " . $e->getMessage());

            $this->kelas_kuliah->update([
                'sync_status' => 'failed',
                'sync_message' => substr($e->getMessage(), 0, 255),
            ]);

            throw $e;
        }

    }
}

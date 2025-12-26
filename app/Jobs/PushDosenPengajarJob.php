<?php

namespace App\Jobs;

use App\Models\DosenPengajarKelasKuliah;
use App\Services\PddiktiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PushDosenPengajarJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public DosenPengajarKelasKuliah $dosenPengajar
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(PddiktiClient $client): void
    {
        try {
            // 1. Check if Kelas Kuliah has id_server
            $kelasKuliah = $this->dosenPengajar->kelasKuliah;

            if (empty($kelasKuliah->id_server)) {
                // Dispatch PushKelasKuliahJob first
                PushKelasKuliahJob::dispatch($kelasKuliah);

                Log::info("PushDosenPengajarJob: Kelas Kuliah {$kelasKuliah->id_kelas_kuliah} belum memiliki id_server. PushKelasKuliahJob dispatched.");

                // Release job to retry later (after 30 seconds)
                $this->release(30);
                return;
            }

            // 2. Determine id_registrasi_dosen - use id_dosen_alias if exists
            $idRegistrasiDosen = !empty($this->dosenPengajar->id_dosen_alias)
                ? $this->dosenPengajar->id_dosen_alias
                : $this->dosenPengajar->id_registrasi_dosen;

            // 3. Prepare payload
            $payload = [
                'id_kelas_kuliah' => $kelasKuliah->id_server,
                'id_registrasi_dosen' => $idRegistrasiDosen,
                'sks_substansi_total' => $this->dosenPengajar->sks_substansi_total,
                'rencana_minggu_pertemuan' => $this->dosenPengajar->rencana_minggu_pertemuan,
                'realisasi_minggu_pertemuan' => $this->dosenPengajar->realisasi_minggu_pertemuan,
                'id_jenis_evaluasi' => $this->dosenPengajar->id_jenis_evaluasi,
            ];

            // 4. Call API
            $response = $client->insertDosenPengajarKelasKuliah($payload);

            Log::info("PushDosenPengajarJob: Berhasil push dosen pengajar {$this->dosenPengajar->id}", [
                'response' => $response,
            ]);

        } catch (\Exception $e) {
            Log::error("PushDosenPengajarJob: Failed to push dosen pengajar {$this->dosenPengajar->id}: " . $e->getMessage());

            throw $e;
        }
    }
}

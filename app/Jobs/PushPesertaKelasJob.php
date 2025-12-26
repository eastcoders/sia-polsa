<?php

namespace App\Jobs;

use App\Models\PesertaKelasKuliah;
use App\Services\PddiktiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PushPesertaKelasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public PesertaKelasKuliah $pesertaKelas
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(PddiktiClient $client): void
    {
        try {
            // 1. Check if Kelas Kuliah has id_server
            $kelasKuliah = $this->pesertaKelas->kelasKuliah;

            if (empty($kelasKuliah->id_server)) {
                // Dispatch PushKelasKuliahJob first
                PushKelasKuliahJob::dispatch($kelasKuliah);

                Log::info("PushPesertaKelasJob: Kelas Kuliah {$kelasKuliah->id_kelas_kuliah} belum memiliki id_server. PushKelasKuliahJob dispatched.");

                // Release job to retry later (after 30 seconds)
                $this->release(30);
                return;
            }

            // 2. Check if Riwayat Pendidikan has id_server
            $riwayatPendidikan = $this->pesertaKelas->riwayatPendidikan;

            if (!$riwayatPendidikan || empty($riwayatPendidikan->id_server)) {
                $message = "Peserta Kelas {$this->pesertaKelas->id} tidak memiliki Riwayat Pendidikan dengan id_server.";
                Log::warning("PushPesertaKelasJob: " . $message);

                throw new \Exception($message);
            }

            // 3. Prepare payload
            $payload = [
                'id_kelas_kuliah' => $kelasKuliah->id_server,
                'id_registrasi_mahasiswa' => $riwayatPendidikan->id_server,
            ];

            // 4. Call API
            $response = $client->insertPesertaKelasKuliah($payload);

            Log::info("PushPesertaKelasJob: Berhasil push peserta kelas {$this->pesertaKelas->id}", [
                'response' => $response,
            ]);

        } catch (\Exception $e) {
            Log::error("PushPesertaKelasJob: Failed to push peserta kelas {$this->pesertaKelas->id}: " . $e->getMessage());

            throw $e;
        }
    }
}

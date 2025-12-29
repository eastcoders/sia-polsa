<?php

namespace App\Jobs;

use App\Models\AktivitasKuliahMahasiswa;
use App\Services\PddiktiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PushAktivitasKuliahMahasiswaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public AktivitasKuliahMahasiswa $akm
    ) {}

    public function handle(PddiktiClient $client): void
    {
        try {
            $this->akm->load('riwayatPendidikan');
            $riwayat = $this->akm->riwayatPendidikan;

            if (! $riwayat || empty($riwayat->id_server)) {
                // Bisa dispatch PushRiwayatPendidikanJob jika ada, atau fail
                throw new \Exception("AKM {$this->akm->id}: Riwayat Pendidikan belum memiliki id_server.");
            }

            // Prepare Payload
            $payload = [
                'id_registrasi_mahasiswa' => $riwayat->id_server,
                'id_semester' => $this->akm->id_semester,
                'id_status_mahasiswa' => $this->akm->id_status_mahasiswa,
                'ips' => $this->akm->ips,
                'ipk' => $this->akm->ipk,
                'sks_semester' => $this->akm->sks_semester,
                'sks_total' => $this->akm->sks_total,
                'biaya_kuliah_smt' => $this->akm->biaya_kuliah_smt ?? 0,
            ];

            // Insert (Assuming Insert logic for now)
            $response = $client->insertAktivitasKuliahMahasiswa($payload);

            // Update local with id_server if returned (Feeder insert returns record)
            $this->akm->update([
                // 'id_server' => $response['id_aktivitas_kuliah'] ?? null, // Check response structure
                'updated_at' => now(), // Just mark synced implicitly or add sync columns to AKM table?
            ]);
            // Note: My migration created id_server column. Ideally we save it.
            if (isset($response['id_aktivitas_kuliah_mahasiswa'])) {
                $this->akm->update(['id_server' => $response['id_aktivitas_kuliah_mahasiswa']]);
            }

            Log::info("PushAktivitasKuliahMahasiswaJob: Success push AKM {$this->akm->id}");

        } catch (\Exception $e) {
            Log::error("PushAktivitasKuliahMahasiswaJob: Failed {$this->akm->id}: ".$e->getMessage());
            throw $e;
        }
    }
}

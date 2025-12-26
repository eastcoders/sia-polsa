<?php

namespace App\Jobs;

use App\Models\NilaiKelasPerkuliahan;
use App\Services\PddiktiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PushNilaiPerkuliahanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public NilaiKelasPerkuliahan $nilaiPerkuliahan
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(PddiktiClient $client): void
    {
        try {
            // 1. Load relasi yang diperlukan
            $this->nilaiPerkuliahan->load(['kelasKuliah']);
            $kelasKuliah = $this->nilaiPerkuliahan->kelasKuliah;

            // 2. Cek apakah Kelas Kuliah memiliki id_server
            if (!$kelasKuliah || empty($kelasKuliah->id_server)) {
                if ($kelasKuliah) {
                    // Dispatch PushKelasKuliahJob terlebih dahulu
                    PushKelasKuliahJob::dispatch($kelasKuliah);

                    Log::info("PushNilaiPerkuliahanJob: Kelas Kuliah {$kelasKuliah->id_kelas_kuliah} belum memiliki id_server. PushKelasKuliahJob dispatched.");
                } else {
                    Log::warning("PushNilaiPerkuliahanJob: Kelas Kuliah tidak ditemukan untuk nilai {$this->nilaiPerkuliahan->id}");
                }

                // Release job untuk retry nanti (setelah 30 detik)
                $this->release(30);
                return;
            }

            // 3. Cari RiwayatPendidikan berdasarkan id_registrasi_mahasiswa
            $riwayatPendidikan = \App\Models\RiwayatPendidikan::where(
                'id_registrasi_mahasiswa',
                $this->nilaiPerkuliahan->id_registrasi_mahasiswa
            )->first();



            if (!$riwayatPendidikan || empty($riwayatPendidikan->id_server)) {
                $message = "Nilai Perkuliahan {$this->nilaiPerkuliahan->id} tidak memiliki Riwayat Pendidikan dengan id_server (id_registrasi_mahasiswa: {$this->nilaiPerkuliahan->id_registrasi_mahasiswa}).";
                Log::warning("PushNilaiPerkuliahanJob: " . $message);

                // Mark as failed
                $this->nilaiPerkuliahan->update([
                    'sync_status' => 'failed',
                    'sync_message' => 'Riwayat Pendidikan tidak memiliki id_server',
                ]);

                throw new \Exception($message);
            }

            // 4. Validasi: pastikan ada nilai yang akan di-push
            if (
                $this->nilaiPerkuliahan->nilai_angka === null &&
                $this->nilaiPerkuliahan->nilai_huruf === null &&
                $this->nilaiPerkuliahan->nilai_indeks === null
            ) {
                Log::info("PushNilaiPerkuliahanJob: Skip push nilai {$this->nilaiPerkuliahan->id} - semua nilai kosong.");
                return;
            }

            // 5. Prepare Key (Primary Key untuk identifikasi record)
            $key = [
                'id_kelas_kuliah' => $kelasKuliah->id_server,
                'id_registrasi_mahasiswa' => $riwayatPendidikan->id_server,
            ];

            // 6. Prepare Data (Data yang akan diupdate)
            $data = [
                'nilai_angka' => $this->nilaiPerkuliahan->nilai_angka,
                'nilai_indeks' => $this->nilaiPerkuliahan->nilai_indeks,
                'nilai_huruf' => $this->nilaiPerkuliahan->nilai_huruf,
            ];

            // 7. Call API
            $response = $client->updateNilaiPerkuliahanKelas($key, $data);


            // 7. Update status sync
            $this->nilaiPerkuliahan->update([
                'sync_at' => now(),
                'sync_status' => 'synced',
                'sync_message' => null,
            ]);

            Log::info("PushNilaiPerkuliahanJob: Berhasil push nilai perkuliahan {$this->nilaiPerkuliahan->id}", [
                'key' => $key,
                'data' => $data,
                'response' => $response,
            ]);

        } catch (\Exception $e) {
            // Update status gagal
            $this->nilaiPerkuliahan->update([
                'sync_status' => 'failed',
                'sync_message' => $e->getMessage(),
            ]);

            Log::error("PushNilaiPerkuliahanJob: Failed to push nilai perkuliahan {$this->nilaiPerkuliahan->id}: " . $e->getMessage());

            throw $e;
        }
    }
}

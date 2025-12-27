<?php

namespace App\Jobs;

use App\Models\BiodataMahasiswa;
use App\Services\PddiktiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PushBiodataMahasiswaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public BiodataMahasiswa $mahasiswa
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(PddiktiClient $client): void
    {
        try {
            // 1. Prepare Biodata Payload
            $biodataPayload = [
                'nama_mahasiswa' => $this->mahasiswa->nama_lengkap,
                'jenis_kelamin' => $this->mahasiswa->jenis_kelamin,
                'tempat_lahir' => $this->mahasiswa->tempat_lahir,
                'tanggal_lahir' => $this->mahasiswa->tanggal_lahir->format('Y-m-d'),
                'id_agama' => $this->mahasiswa->id_agama,
                'nik' => $this->mahasiswa->nik,
                'nisn' => $this->mahasiswa->nisn,
                'kewarganegaraan' => $this->mahasiswa->kewarganegaraan == 'Indonesia' ? 'ID' : null,
                'jalan' => $this->mahasiswa->jalan,
                'rt' => $this->mahasiswa->rt,
                'rw' => $this->mahasiswa->rw,
                'dusun' => $this->mahasiswa->dusun,
                'kelurahan' => $this->mahasiswa->kelurahan,
                'kode_pos' => $this->mahasiswa->kode_pos,
                'id_wilayah' => $this->mahasiswa->id_wilayah,
                'id_jenis_tinggal' => $this->mahasiswa->id_jenis_tinggal,
                'telepon' => $this->mahasiswa->telepone,
                'handphone' => $this->mahasiswa->no_hp,
                'email' => $this->mahasiswa->email,
                'penerima_kps' => $this->mahasiswa->penerima_kps,
                'nomor_kps' => $this->mahasiswa->no_kps,
                'nik_ayah' => $this->mahasiswa->nik_ayah,
                'nama_ayah' => $this->mahasiswa->nama_ayah,
                'tanggal_lahir_ayah' => $this->mahasiswa->tanggal_lahir_ayah?->format('Y-m-d'),
                'id_pendidikan_ayah' => $this->mahasiswa->id_pendidikan_ayah,
                'id_pekerjaan_ayah' => $this->mahasiswa->id_pekerjaan_ayah,
                'id_penghasilan_ayah' => $this->mahasiswa->id_penghasilan_ayah,
                'nik_ibu' => $this->mahasiswa->nik_ibu,
                'nama_ibu_kandung' => $this->mahasiswa->nama_ibu_kandung,
                'tanggal_lahir_ibu' => $this->mahasiswa->tanggal_lahir_ibu?->format('Y-m-d'),
                'id_pendidikan_ibu' => $this->mahasiswa->id_pendidikan_ibu,
                'id_pekerjaan_ibu' => $this->mahasiswa->id_pekerjaan_ibu,
                'id_penghasilan_ibu' => $this->mahasiswa->id_penghasilan_ibu,
                'nama_wali' => $this->mahasiswa->nama_wali,
                'id_pendidikan_wali' => $this->mahasiswa->id_pendidikan_wali,
                'id_pekerjaan_wali' => $this->mahasiswa->id_pekerjaan_wali,
                'id_penghasilan_wali' => $this->mahasiswa->id_penghasilan_wali,
                'id_kebutuhan_khusus_mahasiswa' => $this->mahasiswa->id_kebutuhan_khusus_mahasiswa ?? 0,
                'id_kebutuhan_khusus_ayah' => $this->mahasiswa->id_kebutuhan_khusus_ayah ?? 0,
                'id_kebutuhan_khusus_ibu' => $this->mahasiswa->id_kebutuhan_khusus_ibu ?? 0,
            ];

            // 2. Push Biodata
            if (empty($this->mahasiswa->id_server)) {
                $response = $client->insertBiodataMahasiswa($biodataPayload);
                // Assume response structure: ['id_mahasiswa' => 'UUID'] or similar
                $idServer = $response['id_mahasiswa'] ?? $response['id'] ?? null;

                if (!$idServer) {
                    throw new \Exception("Gagal mendapatkan ID Server setelah insert Biodata.");
                }

                $this->mahasiswa->update([
                    'id_server' => $idServer,
                    'sync_status' => 'synced', // Temporary, wait for Riwayat
                    'sync_message' => null,
                    'sync_at' => now(),
                ]);
            } else {
                $idServer = $this->mahasiswa->id_server;

                $response = $client->updateBiodataMahasiswa([
                    'key' => [
                        'id_mahasiswa' => $idServer
                    ],
                    'record' => $biodataPayload,
                ]);

                if (!$response) {
                    throw new \Exception("Gagal update Biodata.");
                }

                $this->mahasiswa->update([
                    'sync_status' => 'synced',
                    'sync_message' => null,
                    'sync_at' => now(),
                ]);
            }

            // 3. Push Riwayat Pendidikan
            // Load Riwayat that are not synced yet or all? Strategy: Push All active Riwayat
            $riwayat = $this->mahasiswa->riwayatPendidikan; // HasOne relationship

            if ($riwayat) {
                $riwayatPayload = [
                    'id_mahasiswa' => $idServer,
                    'nim' => $riwayat->nim,
                    'id_jenis_daftar' => $riwayat->id_jenis_daftar,
                    'id_jalur_daftar' => $riwayat->id_jalur_daftar,
                    'id_periode_masuk' => $riwayat->id_periode_masuk,
                    'tanggal_daftar' => $riwayat->tanggal_daftar, // Y-m-d
                    'id_perguruan_tinggi' => $riwayat->id_perguruan_tinggi, // From Env/Config usually
                    'id_prodi' => $riwayat->id_prodi,
                    'id_bidang_minat' => $riwayat->id_bidang_minat,
                    'sks_diakui' => $riwayat->sks_diakui,
                    'id_perguruan_tinggi_asal' => $riwayat->id_perguruan_tinggi_asal,
                    'id_prodi_asal' => $riwayat->id_prodi_asal,
                    'id_pembiayaan' => $riwayat->id_pembiayaan,
                    'biaya_masuk' => $riwayat->biaya_masuk,
                ];

                if (empty($riwayat->id_server)) {
                    $riwayatResponse = $client->insertRiwayatPendidikanMahasiswa($riwayatPayload);
                    $idRiwayatServer = $riwayatResponse['id_registrasi_mahasiswa'] ?? $riwayatResponse['id'] ?? null;

                    if ($idRiwayatServer) {
                        $riwayat->update([
                            'id_server' => $idRiwayatServer,
                            'sync_status' => 'synced',
                            'sync_message' => null,
                            'sync_at' => now(),
                        ]);
                    }
                }
            }

            // Final Update Success
            $this->mahasiswa->update([
                'sync_status' => 'synced',
                'sync_message' => 'Data pushed successfully',
                'sync_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to push mahasiswa {$this->mahasiswa->id}: " . $e->getMessage());

            $this->mahasiswa->update([
                'sync_status' => 'failed',
                'sync_message' => substr($e->getMessage(), 0, 255),
            ]);

            throw $e; // Trigger retry
        }
    }
}

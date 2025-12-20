<?php

namespace App\Jobs;

use App\Models\BiodataMahasiswa;
use App\Models\RiwayatPendidikan;
use App\Services\PddiktiClient;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncMahasiswaPageJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $limit,
        public int $offset,
        public array $filter = []
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(PddiktiClient $client): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        try {
            $data = $client->getBiodataMahasiswa([
                'filter' => $this->filter['filter'] ?? '',
                'limit' => $this->limit,
                'offset' => $this->offset,
            ]);

            DB::transaction(function () use ($data) {
                foreach ($data as $row) {
                    $mahasiswa = BiodataMahasiswa::updateOrCreate(
                        ['id_server' => $row['id_mahasiswa']], // Mapping Column Strategy
                        [
                            // 'id_mahasiswa' => $row['id_mahasiswa'],
                            'nama_lengkap' => $row['nama_mahasiswa'] ?? '-',
                            'jenis_kelamin' => $row['jenis_kelamin'] ?? '-',
                            'tanggal_lahir' => $row['tanggal_lahir'] ?? '-',
                            'tempat_lahir' => $row['tempat_lahir'] ?? '01-01-1999',
                            'id_agama' => $row['id_agama'] ?? '99',
                            'nik' => $row['nik'] ?? null,
                            'nisn' => $row['nisn'] ?? null,
                            'npwp' => $row['npwp'] ?? null,
                            'kewarganegaraan' => $row['kewarganegaraan'] ?? '-',
                            'jalan' => $row['jalan'] ?? '-',
                            'dusun' => $row['dusun'] ?? '-',
                            'rt' => $row['rt'] ?? '-',
                            'rw' => $row['rw'] ?? '-',
                            'kelurahan' => $row['kelurahan'] ?? '-',
                            'kode_pos' => $row['kode_pos'] ?? '-',
                            'id_wilayah' => trim($row['id_wilayah']) ?? '-',
                            'id_jenis_tinggal' => $row['id_jenis_tinggal'] ?? '-',
                            'telepone' => $row['telepon'] ?? null,
                            'no_hp' => $row['handphone'] ?? null,
                            'email' => $row['email'] ?? null,
                            'penerima_kps' => $row['penerima_kps'] ?? '-',
                            'no_kps' => $row['nomor_kps'] ?? '-',
                            'nik_ayah' => $row['nik_ayah'] ?? '-',
                            'nama_ayah' => $row['nama_ayah'] ?? '-',
                            'tanggal_lahir_ayah' => $row['tanggal_lahir_ayah'] ?? '01-01-1999',
                            'id_pendidikan_ayah' => $row['id_pendidikan_ayah'] ?? '-',
                            'id_pekerjaan_ayah' => $row['id_pekerjaan_ayah'] ?? '-',
                            'id_penghasilan_ayah' => $row['id_penghasilan_ayah'] ?? '-',
                            'nik_ibu' => $row['nik_ibu'] ?? '-',
                            'nama_ibu_kandung' => $row['nama_ibu_kandung'] ?? '-',
                            'tanggal_lahir_ibu' => $row['tanggal_lahir_ibu'] ?? '01-01-1999',
                            'id_pendidikan_ibu' => $row['id_pendidikan_ibu'] ?? '-',
                            'id_pekerjaan_ibu' => $row['id_pekerjaan_ibu'] ?? '-',
                            'id_penghasilan_ibu' => $row['id_penghasilan_ibu'] ?? '-',
                            'nama_wali' => $row['nama_wali'] ?? '-',
                            'id_pendidikan_wali' => $row['id_pendidikan_wali'] ?? '-',
                            'id_pekerjaan_wali' => $row['id_pekerjaan_wali'] ?? '-',
                            'id_penghasilan_wali' => $row['id_penghasilan_wali'] ?? '-',
                            'id_kebutuhan_khusus_mahasiswa' => $row['id_kebutuhan_khusus_mahasiswa'] ?? '-',
                            'id_kebutuhan_khusus_ayah' => $row['id_kebutuhan_khusus_ayah'] ?? '-',
                            'id_kebutuhan_khusus_ibu' => $row['id_kebutuhan_khusus_ibu'] ?? '-',
                            'sync_at' => now(),
                            'sync_status' => 'synced',
                            'sync_message' => null,
                        ]
                    );

                    // Sync Riwayat Pendidikan (Optional: Bisa dipisah ke Job anak lain jika terlalu berat)
                    // Untuk saat ini disatukan karena biasanya data Riwayat nempel di Biodata
                    if (isset($row['riwayat_pendidikan']) && is_array($row['riwayat_pendidikan'])) {
                        foreach ($row['riwayat_pendidikan'] as $riwayat) {
                            RiwayatPendidikan::updateOrCreate(
                                ['id_server' => $riwayat['id_registrasi_mahasiswa']],
                                [
                                    'id_mahasiswa' => $mahasiswa->id_mahasiswa, // Relasi Lokal
                                    'nim' => $riwayat['nim'],
                                    'id_jenis_daftar' => $riwayat['id_jenis_daftar'],
                                    'id_jalur_daftar' => $riwayat['id_jalur_daftar'],
                                    'id_periode_masuk' => $riwayat['id_periode_masuk'],
                                    'tanggal_daftar' => $riwayat['tanggal_daftar'],
                                    'id_prodi' => $riwayat['id_prodi'],
                                    'sks_diakui' => $riwayat['sks_diakui'],
                                    'id_perguruan_tinggi_asal' => $riwayat['id_perguruan_tinggi_asal'],
                                    'id_prodi_asal' => $riwayat['id_prodi_asal'],
                                    'id_pembiayaan' => $riwayat['id_pembiayaan'],
                                    'biaya_masuk' => $riwayat['biaya_masuk'],
                                    'sync_at' => now(),
                                    'sync_status' => 'synced',
                                ]
                            );
                        }
                    }
                }
            });

        } catch (\Exception $e) {
            Log::error("Failed to sync page offset {$this->offset}: " . $e->getMessage());
            throw $e; // Re-throw to mark batch as failure
        }
    }
}

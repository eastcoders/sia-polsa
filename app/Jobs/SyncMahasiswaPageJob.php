<?php

namespace App\Jobs;

use App\Models\BiodataMahasiswa;
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
    ) {}

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

            $successCount = 0;
            $errorCount = 0;

            foreach ($data as $row) {
                try {
                    DB::transaction(function () use ($row) {
                        // Cek apakah record sudah ada
                        $existing = BiodataMahasiswa::where('id_server', $row['id_mahasiswa'])->first();

                        $updateData = [
                            'nama_lengkap' => $row['nama_mahasiswa'] ?? null,
                            'jenis_kelamin' => $row['jenis_kelamin'] ?? null,
                            'tanggal_lahir' => $row['tanggal_lahir'] ?? null,
                            'tempat_lahir' => $row['tempat_lahir'] ?? '01-01-0001',
                            'id_agama' => $row['id_agama'] ?? '99',
                            'nik' => $row['nik'] ?? null,
                            'nisn' => $row['nisn'] ?? null,
                            'npwp' => $row['npwp'] ?? null,
                            'kewarganegaraan' => $row['kewarganegaraan'] ?? null,
                            'jalan' => $row['jalan'] ?? null,
                            'dusun' => $row['dusun'] ?? null,
                            'rt' => $row['rt'] ?? null,
                            'rw' => $row['rw'] ?? null,
                            'kelurahan' => $row['kelurahan'] ?? null,
                            'kode_pos' => $row['kode_pos'] ?? null,
                            'id_wilayah' => trim($row['id_wilayah']) ?? null,
                            'id_jenis_tinggal' => $row['id_jenis_tinggal'] ?? null,
                            'telepone' => $row['telepon'] ?? null,
                            'no_hp' => $row['handphone'] ?? null,
                            'email' => $row['email'] ?? null,
                            'penerima_kps' => $row['penerima_kps'] ?? null,
                            'no_kps' => $row['nomor_kps'] ?? null,
                            'nik_ayah' => $row['nik_ayah'] ?? null,
                            'nama_ayah' => $row['nama_ayah'] ?? null,
                            'tanggal_lahir_ayah' => $row['tanggal_lahir_ayah'] ?? '01-01-1999',
                            'id_pendidikan_ayah' => $row['id_pendidikan_ayah'] ?? null,
                            'id_pekerjaan_ayah' => $row['id_pekerjaan_ayah'] ?? null,
                            'id_penghasilan_ayah' => $row['id_penghasilan_ayah'] ?? null,
                            'nik_ibu' => $row['nik_ibu'] ?? null,
                            'nama_ibu_kandung' => $row['nama_ibu_kandung'] ?? null,
                            'tanggal_lahir_ibu' => $row['tanggal_lahir_ibu'] ?? '01-01-1999',
                            'id_pendidikan_ibu' => $row['id_pendidikan_ibu'] ?? null,
                            'id_pekerjaan_ibu' => $row['id_pekerjaan_ibu'] ?? null,
                            'id_penghasilan_ibu' => $row['id_penghasilan_ibu'] ?? null,
                            'nama_wali' => $row['nama_wali'] ?? null,
                            'id_pendidikan_wali' => $row['id_pendidikan_wali'] ?? null,
                            'id_pekerjaan_wali' => $row['id_pekerjaan_wali'] ?? null,
                            'id_penghasilan_wali' => $row['id_penghasilan_wali'] ?? null,
                            'id_kebutuhan_khusus_mahasiswa' => $row['id_kebutuhan_khusus_mahasiswa'] ?? '0',
                            'id_kebutuhan_khusus_ayah' => $row['id_kebutuhan_khusus_ayah'] ?? '0',
                            'id_kebutuhan_khusus_ibu' => $row['id_kebutuhan_khusus_ibu'] ?? '0',
                            'sync_at' => now(),
                            'sync_status' => 'synced',
                            'sync_message' => null,
                        ];

                        // Strategi ID aman: Hanya set id_mahasiswa jika record baru atau kolom kosong
                        if (! $existing || empty($existing->id_mahasiswa)) {
                            $updateData['id_mahasiswa'] = $row['id_mahasiswa'];
                        }

                        BiodataMahasiswa::updateOrCreate(
                            ['id_server' => $row['id_mahasiswa']],
                            $updateData
                        );
                    });
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::warning("SyncMahasiswaPageJob: Failed to sync record {$row['id_mahasiswa']}: ".$e->getMessage());
                    // Continue to next record - don't throw
                }
            }

            Log::info("SyncMahasiswaPageJob offset {$this->offset}: {$successCount} success, {$errorCount} errors.");

        } catch (\Exception $e) {
            Log::error("Failed to fetch data for sync page offset {$this->offset}: ".$e->getMessage());
            throw $e; // Re-throw only for API fetch errors
        }
    }
}

<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use App\Services\PddiktiClient;
use App\Models\RiwayatPendidikan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncRiwayatPendidikanPageJob implements ShouldQueue
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
            $data = $client->getListRiwayatPendidikanMahasiswa([
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
                        $existing = RiwayatPendidikan::where('id_server', $row['id_registrasi_mahasiswa'])->first();

                        $updateData = [
                            'nim' => $row['nim'] ?? null,
                            'id_jenis_daftar' => $row['id_jenis_daftar'] ?? null,
                            'id_jalur_daftar' => $row['id_jalur_daftar'] ?? null,
                            'id_periode_masuk' => $row['id_periode_masuk'] ?? null,
                            'tanggal_daftar' => isset($row['tanggal_daftar'])
                                ? Carbon::parse($row['tanggal_daftar'])->format('Y-m-d')
                                : null,
                            'id_prodi' => $row['id_prodi'] ?? null,
                            'id_perguruan_tinggi' => $row['id_perguruan_tinggi'] ?? config('pddikti.id_pt'),
                            'sks_diakui' => $row['sks_diakui'] ?? 0,
                            'id_perguruan_tinggi_asal' => $row['id_perguruan_tinggi_asal'] ?? null,
                            'id_prodi_asal' => $row['id_prodi_asal'] ?? null,
                            'id_pembiayaan' => $row['id_pembiayaan'] ?? null,
                            'biaya_masuk' => $row['biaya_masuk'] ?? 0,
                            'sync_at' => now(),
                            'sync_status' => 'synced',
                            'sync_message' => null,
                            'id_biodata_mahasiswa' => $row['id_registrasi_mahasiswa'],
                        ];

                        // Strategi ID aman: Hanya set ID jika record baru atau kolom kosong
                        if (!$existing || empty($existing->id_registrasi_mahasiswa)) {
                            $updateData['id_registrasi_mahasiswa'] = $row['id_registrasi_mahasiswa'];
                        }

                        if (!$existing || empty($existing->id_mahasiswa)) {
                            $updateData['id_mahasiswa'] = $row['id_mahasiswa'];
                        }

                        RiwayatPendidikan::updateOrCreate(
                            ['id_server' => $row['id_registrasi_mahasiswa']],
                            $updateData
                        );
                    });
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::warning("SyncRiwayatPendidikanPageJob: Failed to sync record {$row['id_registrasi_mahasiswa']}: " . $e->getMessage());
                    // Continue to next record - don't throw
                }
            }

            Log::info("SyncRiwayatPendidikanPageJob offset {$this->offset}: {$successCount} success, {$errorCount} errors.");

        } catch (\Exception $e) {
            Log::error("Failed to fetch data for sync riwayat pendidikan page offset {$this->offset}: " . $e->getMessage());
            throw $e; // Re-throw only for API fetch errors
        }
    }
}

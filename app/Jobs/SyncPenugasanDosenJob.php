<?php

namespace App\Jobs;

use App\Models\PenugasanDosen;
use App\Services\PddiktiClient;
use Carbon\Carbon;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncPenugasanDosenJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $limit,
        public int $offset,
        public array $filter = [],
        public bool $recursive = false,
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
            $data = $client->getListPenugasanDosen([
                'filter' => $this->filter['filter'] ?? '',
                'limit' => $this->limit,
                'offset' => $this->offset,
            ]);

            DB::transaction(function () use ($data) {
                foreach ($data as $row) {
                    $tanggalSuratTugas = $row['tanggal_surat_tugas'] ?? null;
                    $mulaiSuratTugas = $row['mulai_surat_tugas'] ?? null;

                    $tanggalSuratTugasFormatted = $tanggalSuratTugas ? Carbon::createFromFormat('d-m-Y', $tanggalSuratTugas)->format('Y-m-d') : null;
                    $mulaiSuratTugasFormatted = $mulaiSuratTugas ? Carbon::createFromFormat('d-m-Y', $mulaiSuratTugas)->format('Y-m-d') : null;

                    PenugasanDosen::updateOrCreate(
                        [
                            'id_registrasi_dosen' => $row['id_registrasi_dosen'],
                        ],
                        [
                            'id_dosen' => $row['id_dosen'],
                            'id_prodi' => $row['id_prodi'],
                            'id_tahun_ajaran' => $row['id_tahun_ajaran'],
                            'id_perguruan_tinggi' => $row['id_perguruan_tinggi'],
                            'nomor_surat_tugas' => $row['nomor_surat_tugas'],
                            'tanggal_surat_tugas' => $tanggalSuratTugasFormatted,
                            'mulai_surat_tugas' => $mulaiSuratTugasFormatted,
                            'sync_at' => now(),
                        ]
                    );
                }
            });

            // Handle Recursive Logic
            if ($this->recursive && count($data) >= $this->limit) {
                $this->batch()->add([
                    new SyncPenugasanDosenJob(
                        $this->limit,
                        $this->offset + $this->limit,
                        $this->filter,
                        true
                    ),
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to sync penugasan dosen at offset {$this->offset}: " . $e->getMessage());
            throw $e;
        }
    }
}

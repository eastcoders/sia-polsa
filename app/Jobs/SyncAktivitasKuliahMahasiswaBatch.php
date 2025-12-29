<?php

namespace App\Jobs;

use App\Models\AktivitasKuliahMahasiswa;
use App\Services\PddiktiClient;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncAktivitasKuliahMahasiswaBatch extends BaseSyncJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(PddiktiClient $client): void
    {
        if ($this->isCancelled()) {
            return;
        }

        try {
            $data = $client->getListPerkuliahanMahasiswa([
                'filter' => $this->filter['filter'] ?? '',
                'limit' => $this->limit,
                'offset' => $this->offset,
            ]);

            $this->syncWithPerRecordHandling($data, function ($row) {
                AktivitasKuliahMahasiswa::updateOrCreate(
                    ['id_registrasi_mahasiswa' => $row['id_registrasi_mahasiswa'], 'id_semester' => $row['id_semester']],
                    [
                        'id_status_mahasiswa' => $row['id_status_mahasiswa'],
                        'ips' => $row['ips'],
                        'ipk' => $row['ipk'],
                        'sks_semester' => $row['sks_semester'],
                        'sks_total' => $row['sks_total'],
                        'biaya_kuliah_smt' => $row['biaya_kuliah_smt'],
                        'id_pembiayaan' => $row['id_pembiayaan'] ?? null,
                        'id_server' => $row['id_registrasi_mahasiswa'], // Usually referencing ID for AKM is composite, but assuming local tracking needs something
                        'sync_at' => now(),
                        'sync_status' => 'synced',
                        'sync_message' => null,
                    ]
                );
            });

        } catch (\Exception $e) {
            // Log handled by BaseSyncJob if inside loop, but outer errors (API fetch) need throwing or logging
            throw $e;
        }
    }

    protected function getJobName(): string
    {
        return 'SyncAktivitasKuliahMahasiswaBatch';
    }

    protected function getRecordId(array $row): string
    {
        return ($row['id_registrasi_mahasiswa'] ?? '?').'-'.($row['id_semester'] ?? '?');
    }
}

<?php

namespace App\Jobs;

use App\Models\RiwayatPendidikan;
use App\Services\PddiktiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PushKelulusanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public RiwayatPendidikan $riwayat
    ) {}

    public function handle(PddiktiClient $client): void
    {
        try {
            if (empty($this->riwayat->id_server)) {
                throw new \Exception("Riwayat Pendidikan {$this->riwayat->id} belum memiliki id_server.");
            }

            if (empty($this->riwayat->id_jenis_keluar)) {
                Log::warning("PushKelulusanJob: Skip {$this->riwayat->id} - id_jenis_keluar kosong.");

                return;
            }

            // Prepare Key & Record (Update Mode)
            $key = [
                'id_registrasi_mahasiswa' => $this->riwayat->id_server,
            ];

            $record = [
                'id_jenis_keluar' => $this->riwayat->id_jenis_keluar,
                'tanggal_keluar' => $this->riwayat->tanggal_keluar?->format('Y-m-d'),
                'keterangan_keluar' => $this->riwayat->keterangan_keluar,
                'no_seri_ijazah' => $this->riwayat->no_seri_ijazah,
            ];

            // Call Update
            $client->updateRiwayatPendidikanMahasiswa($key, $record);

            // Update Sync Status
            $this->riwayat->update([
                'sync_at' => now(),
                'sync_status' => 'synced',
            ]);

            Log::info("PushKelulusanJob: Success push kelulusan {$this->riwayat->id}");

        } catch (\Exception $e) {
            Log::error("PushKelulusanJob: Failed {$this->riwayat->id}: ".$e->getMessage());
            $this->riwayat->update([
                'sync_status' => 'failed',
                'sync_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

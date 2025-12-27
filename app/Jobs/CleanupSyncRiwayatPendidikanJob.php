<?php

namespace App\Jobs;

use App\Models\RiwayatPendidikan;
use App\Services\PddiktiClient;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CleanupSyncRiwayatPendidikanJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Carbon $syncStartTime,
        public array $filter = []
    ) {
    }

    public function handle(PddiktiClient $client): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        try {
            // 1. Identify Suspects ("Orphaned Records")
            // Records yang punya ID Server (synced/pushed) tapi tidak diupdate di sync session ini
            $suspects = RiwayatPendidikan::whereNotNull('id_server')
                ->where('sync_at', '<', $this->syncStartTime)
                ->where('sync_status', '!=', 'server_deleted')
                ->get();

            $confirmedDeleted = 0;
            $falseAlarms = 0;

            Log::info("Cleanup Sync Riwayat Pendidikan: Checking " . $suspects->count() . " suspects for deletion.");

            foreach ($suspects as $suspect) {
                try {
                    // 2. Verify with API (Explicit Check)
                    $response = $client->getListRiwayatPendidikanMahasiswa([
                        'filter' => "id_registrasi_mahasiswa='{$suspect->id_server}'"
                    ]);

                    // 3. Confirm Deletion
                    if (empty($response)) {
                        $suspect->update([
                            'sync_status' => 'server_deleted',
                            'sync_message' => 'Data tidak ditemukan di server (Verified) saat sinkronisasi ' . $this->syncStartTime->format('d-m-Y H:i'),
                        ]);
                        $confirmedDeleted++;
                    } else {
                        $falseAlarms++;
                    }

                } catch (\Exception $e) {
                    Log::warning("Verification failed for suspect riwayat {$suspect->id_server}: " . $e->getMessage());
                }
            }

            Log::info("Cleanup Sync Riwayat Pendidikan Result: {$confirmedDeleted} confirmed deleted, {$falseAlarms} false alarms.");

        } catch (\Exception $e) {
            Log::error("Failed to run cleanup sync riwayat pendidikan: " . $e->getMessage());
            throw $e;
        }
    }
}

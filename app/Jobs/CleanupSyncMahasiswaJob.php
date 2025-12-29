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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CleanupSyncMahasiswaJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Carbon $syncStartTime,
        public array $filter = []
    ) {}

    public function handle(PddiktiClient $client): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        try {
            // 1. Identify Suspects ("Orphaned Records")
            // Records that have an ID Server (synced/pushed) BUT were not updated in this sync session.
            $suspects = BiodataMahasiswa::whereNotNull('id_server')
                ->where('sync_at', '<', $this->syncStartTime)
                ->where('sync_status', '!=', 'server_deleted') // Avoid re-checking already deleted
                ->get();

            $confirmedDeleted = 0;
            $falseAlarms = 0;

            Log::info('Cleanup Sync: Checking '.$suspects->count().' suspects for deletion.');

            foreach ($suspects as $suspect) {
                try {
                    // 2. Verify with API (Explicit Check)
                    // We check if this specific ID still exists on server.
                    // Filter syntax: id_mahasiswa='UUID'
                    $response = $client->getBiodataMahasiswa([
                        'filter' => "id_mahasiswa='{$suspect->id_server}'",
                    ]);

                    // 3. Confirm Deletion
                    // If response is empty or null, it means data is truly gone from server.
                    if (empty($response)) {
                        $suspect->update([
                            'sync_status' => 'server_deleted',
                            'sync_message' => 'Data tidak ditemukan di server (Verified) saat sinkronisasi '.$this->syncStartTime->format('d-m-Y H:i'),
                        ]);
                        $confirmedDeleted++;
                    } else {
                        // Data exists! It was just a sync miss (maybe Fetch limit? or Network glitch?)
                        // Safe to ignore, or optionally try to sync it now?
                        // For now, we trust the sync process and just don't mark it deleted.
                        $falseAlarms++;
                    }

                } catch (\Exception $e) {
                    Log::warning("Verification failed for suspect {$suspect->id_server}: ".$e->getMessage());
                }
            }

            Log::info("Cleanup Sync Result: {$confirmedDeleted} confirmed deleted, {$falseAlarms} false alarms (exist on server).");

        } catch (\Exception $e) {
            Log::error('Failed to run cleanup sync mahasiswa: '.$e->getMessage());
            throw $e;
        }
    }
}

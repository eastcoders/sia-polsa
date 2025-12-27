<?php

namespace App\Jobs;

use App\Jobs\SyncWilayahJob;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Dispatch semua sync job untuk data master biodata secara berurutan.
 */
class DispatchSyncAllBiodata implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info('DispatchSyncAllBiodata: Starting sync all biodata master data...');

        // Chain semua sync job secara berurutan
        Bus::chain([
            new SyncAgamaJob(),
            new SyncPekerjaanJob(),
            new SyncPenghasilanJob(),
            new SyncJenjangPendidikanJob(),
            new SyncAlatTransportasiJob(),
            new SyncJenisTinggalJob(),
            new SyncWilayahJob(),
        ])->onQueue('default')
            ->dispatch();

        Log::info('DispatchSyncAllBiodata: All biodata sync jobs have been chained.');
    }
}

<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

/**
 * Dispatch semua sync job untuk data master perkuliahan secara berurutan.
 */
class DispatchSyncAllPerkuliahan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info('DispatchSyncAllPerkuliahan: Starting sync all perkuliahan master data...');

        // Chain semua sync job secara berurutan
        Bus::chain([
            new SyncProfilPTJob,
            new SyncAllProdiJob,
            new SyncAllPtJob,
            new SyncProdiJob,
            new SyncSemesterJob,
            new SyncJalurMasukJob,
            new SyncJenisPendaftaranJob,
            new SyncPembiayaanJob,
            new SyncBidangMinatJob,
            new SyncSkalaNilaiJob,
        ])->onQueue('default')
            ->dispatch();

        Log::info('DispatchSyncAllPerkuliahan: All perkuliahan sync jobs have been chained.');
    }
}

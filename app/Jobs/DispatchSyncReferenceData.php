<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class DispatchSyncReferenceData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('DispatchSyncReferenceData: Dispatching batch...');

        $batch = Bus::batch([
            new SyncWilayahJob(),
            // new SyncAllPtJob(), // Optional: PT might be needed first
            // new SyncAllProdiJob(), // Optional: Prodi might depend on PT
        ])
            ->name('Sync Reference Data (Wilayah, PT, Prodi)')
            ->onQueue('high') // Priority queue if available, else default
            ->allowFailures()
            ->finally(function () {
                Log::info('DispatchSyncReferenceData: Batch finished.');
            })
            ->dispatch();

        // Note: Wilayah, PT, dan Prodi adalah data referensi dasar.
        // Sebaiknya PT dan Prodi disync terpisah atau berurutan jika ada relasi Foreign Key yang ketat.
        // Di sini kita masukkan Wilayah dulu. PT dan Prodi mungkin perlu job terpisah atau batch sequence jika sangat strict.
        // Namun, jika tidak ada strict FK constraint di level database yang memblokir insert (misal pakai ignore), bisa paralel.
        // Untuk aman, kita bisa chain atau tambahkan ke batch.
        // Mari kita tambahkan SyncAllPtJob dan SyncAllProdiJob ke batch juga.

        $batch->add([
            new SyncAllPtJob(),
            new SyncAllProdiJob(),
        ]);

        Log::info("DispatchSyncReferenceData: Batch ID {$batch->id} dispatched.");
    }
}

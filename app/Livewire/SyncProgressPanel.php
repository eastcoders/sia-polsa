<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Reactive;
use Livewire\Component;

/**
 * Livewire component untuk menampilkan progress sinkronisasi secara realtime.
 * Menggunakan wire:poll untuk update otomatis setiap 3 detik.
 */
class SyncProgressPanel extends Component
{
    /**
     * Filter nama batch yang akan ditampilkan.
     * Contoh: 'Sync%' untuk semua batch yang dimulai dengan 'Sync'
     */
    public string $batchNameFilter = '%';

    /**
     * Context untuk menentukan warna dan style.
     * Contoh: 'biodata', 'perkuliahan'
     */
    public string $context = 'default';

    /**
     * Jumlah batch terbaru yang akan ditampilkan.
     */
    public int $limit = 5;

    /**
     * Data batch yang sedang berjalan.
     */
    public $activeBatches = [];

    /**
     * Data batch terbaru (selesai/gagal).
     */
    public $recentBatches = [];

    /**
     * Jumlah job di queue.
     */
    public int $pendingJobsCount = 0;

    public function mount(string $batchNameFilter = '%', string $context = 'default', int $limit = 5): void
    {
        $this->batchNameFilter = $batchNameFilter;
        $this->context = $context;
        $this->limit = $limit;
        $this->loadData();
    }

    /**
     * Load data batch dari database.
     */
    public function loadData(): void
    {
        // Batch yang sedang aktif (belum selesai)
        $this->activeBatches = DB::table('job_batches')
            ->where('name', 'like', $this->batchNameFilter)
            ->whereNull('finished_at')
            ->orderByDesc('created_at')
            ->limit($this->limit)
            ->get()
            ->map(function ($batch) {
                $batch->progress = $batch->total_jobs > 0
                    ? round(($batch->total_jobs - $batch->pending_jobs) / $batch->total_jobs * 100, 1)
                    : 0;
                $batch->processed_jobs = $batch->total_jobs - $batch->pending_jobs;
                return $batch;
            })
            ->toArray();

        // Batch terbaru yang sudah selesai
        $this->recentBatches = DB::table('job_batches')
            ->where('name', 'like', $this->batchNameFilter)
            ->whereNotNull('finished_at')
            ->orderByDesc('finished_at')
            ->limit($this->limit)
            ->get()
            ->map(function ($batch) {
                $batch->progress = 100;
                $batch->processed_jobs = $batch->total_jobs;
                $batch->has_failures = $batch->failed_jobs > 0;
                return $batch;
            })
            ->toArray();

        // Hitung job pending di queue
        $this->pendingJobsCount = DB::table('jobs')->count();
    }

    /**
     * Method yang dipanggil saat polling.
     */
    public function poll(): void
    {
        $this->loadData();
    }

    public function render(): View
    {
        return view('livewire.sync-progress-panel');
    }
}

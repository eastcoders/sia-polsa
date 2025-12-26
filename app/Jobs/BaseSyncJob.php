<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Base abstract class for all Sync jobs.
 * Provides per-record error handling to ensure one failed record
 * doesn't cause the entire batch to fail.
 */
abstract class BaseSyncJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $limit;
    public int $offset;
    public array $filter;

    /**
     * Create a new job instance.
     */
    public function __construct(int $limit, int $offset, array $filter = [])
    {
        $this->limit = $limit;
        $this->offset = $offset;
        $this->filter = $filter;
    }

    /**
     * Check if the batch has been cancelled.
     */
    protected function isCancelled(): bool
    {
        return $this->batch()?->cancelled() ?? false;
    }

    /**
     * Get the job name for logging purposes.
     */
    abstract protected function getJobName(): string;

    /**
     * Get the record identifier from a data row for logging.
     */
    abstract protected function getRecordId(array $row): string;

    /**
     * Sync data with per-record error handling.
     * 
     * @param array $data Array of records from API
     * @param callable $syncCallback Function that takes a single $row and syncs it
     * @return array ['success' => int, 'errors' => int]
     */
    protected function syncWithPerRecordHandling(array $data, callable $syncCallback): array
    {
        $successCount = 0;
        $errorCount = 0;

        foreach ($data as $row) {
            try {
                DB::transaction(function () use ($row, $syncCallback) {
                    $syncCallback($row);
                });
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                Log::warning("{$this->getJobName()}: Failed to sync record {$this->getRecordId($row)}: " . $e->getMessage());
                // Continue to next record - don't throw
            }
        }

        Log::info("{$this->getJobName()} offset {$this->offset}: {$successCount} success, {$errorCount} errors.");

        return [
            'success' => $successCount,
            'errors' => $errorCount,
        ];
    }
}

<?php

namespace App\Jobs;

use App\Models\Dosen;
use App\Services\PddiktiClient;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncDosenJob implements ShouldQueue
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
    ) {}

    /**
     * Execute the job.
     */
    public function handle(PddiktiClient $client): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        try {
            $data = $client->getListDosen([
                'filter' => $this->filter['filter'] ?? '',
                'limit' => $this->limit,
                'offset' => $this->offset,
            ]);

            DB::transaction(function () use ($data) {
                foreach ($data as $row) {
                    Dosen::updateOrCreate(
                        [
                            'id_dosen' => $row['id_dosen'],
                        ],
                        [
                            'nama_dosen' => trim($row['nama_dosen']),
                            'nidn' => trim($row['nidn']),
                            'nip' => trim($row['nip']),
                            'jenis_kelamin' => trim($row['jenis_kelamin']),
                            'id_agama' => trim($row['id_agama']),
                            'tanggal_lahir' => trim($row['tanggal_lahir']),
                            'id_status_aktif' => trim($row['id_status_aktif']),
                            'nama_status_aktif' => trim($row['nama_status_aktif']),
                            'sync_at' => now(),
                        ]
                    );
                }
            });

            // Handle Recursive Logic
            if ($this->recursive && count($data) >= $this->limit) {
                $this->batch()->add([
                    new SyncDosenJob(
                        $this->limit,
                        $this->offset + $this->limit,
                        $this->filter,
                        true
                    ),
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to sync dosen at offset {$this->offset}: ".$e->getMessage());
            throw $e;
        }
    }
}

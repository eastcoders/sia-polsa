<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusMahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(\App\Services\PddiktiClient $client): void
    {
        try {
            $data = $client->getStatusMahasiswa();
            if (is_iterable($data)) {
                foreach ($data as $item) {
                    \App\Models\StatusMahasiswa::updateOrCreate(
                        ['id_status_mahasiswa' => $item['id_status_mahasiswa']],
                        ['nama_status_mahasiswa' => $item['nama_status_mahasiswa']]
                    );
                }
                $this->command->info('Status Mahasiswa synced successfully.');
            } else {
                $this->command->error('Failed to retrieve status mahasiswa data.');
            }
        } catch (\Exception $e) {
            $this->command->error('Error syncing status mahasiswa: ' . $e->getMessage());
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Kurikulum;
use App\Models\Prodi;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KurikulumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        for ($i = 1; $i <= 10; $i++) {
            Kurikulum::create([
                'id_kurikulum' => Str::uuid()->toString(),
                'nama_kurikulum' => 'KL-'.$faker->numberBetween(1, 999999),
                'id_prodi' => Prodi::inRandomOrder()->value('id_prodi'),
                'id_semester' => '20251',
                'jumlah_sks_wajib' => '10',
                'jumlah_sks_pilihan' => '14',
                'jumlah_sks_lulus' => '24',
            ]);
        }
    }
}

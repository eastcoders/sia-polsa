<?php

namespace Database\Seeders;

use App\Models\KelasKuliah;
use App\Models\MataKuliah;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KelasKuliahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        if (MataKuliah::count() == 0) {
            $this->call(MataKuliahSeeder::class);
        }

        foreach (MataKuliah::all() as $matkul) {
            KelasKuliah::create([
                'id_kelas_kuliah' => Str::uuid()->toString(),
                'id_prodi' => $matkul->id_prodi,
                'id_semester' => '20251',
                'nama_kelas_kuliah' => 'CLS-'.$faker->numberBetween(1, 10),
                'id_matkul' => $matkul->id_matkul,
                'lingkup' => $faker->randomElement(['1', '2', '3']),
                'mode' => $faker->randomElement(['O', 'F', 'M']),
                'tanggal_mulai_efektif' => $faker->dateTimeBetween('-1 year', '+1 year'),
                'tanggal_akhir_efektif' => $faker->dateTimeBetween('-1 year', '+1 year'),
                'sks_mk' => $matkul->sks_mata_kuliah,
                'sks_tm' => $matkul->sks_tatap_muka,
                'sks_prak' => $matkul->sks_praktek,
                'sks_sim' => $matkul->sks_praktek_lapangan,
            ]);
        }
    }
}

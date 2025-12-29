<?php

namespace Database\Seeders;

use App\Models\Kurikulum;
use App\Models\MataKuliah;
use App\Models\MatkulKurikulum;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class MatkulKurikulumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Kurikulum::count() == 0) {
            $this->call(KurikulumSeeder::class);
        }

        if (MataKuliah::count() == 0) {
            $this->call(MataKuliahSeeder::class);
        }

        $faker = Faker::create('id_ID');

        foreach (Kurikulum::all() as $kurikulum) {

            $allMatkulIds = MataKuliah::where('id_prodi', $kurikulum->id_prodi)
                ->pluck('id_matkul');

            $existingMatkulIds = MatkulKurikulum::where('id_kurikulum', $kurikulum->id_kurikulum)
                ->pluck('id_matkul');

            $availableMatkulIds = $allMatkulIds->diff($existingMatkulIds);

            foreach ($availableMatkulIds as $matkulId) {
                MatkulKurikulum::create([
                    'id_kurikulum' => $kurikulum->id_kurikulum,
                    'id_matkul' => $matkulId,
                    'semester' => $faker->numberBetween(1, 8),
                    'apakah_wajib' => $faker->randomElement(['0', '1']),
                ]);
            }
        }

    }
}

<?php

namespace Database\Seeders;

use App\Models\Agama;
use App\Models\Dosen;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;


class DosenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        for ($i = 0; $i < 10; $i++) {
            Dosen::create([
                'id_dosen' => Str::uuid()->toString(),
                'nama_dosen' => $faker->name(),
                'jenis_kelamin' => $faker->randomElement(['L', 'P']),
                'id_agama' => Agama::inRandomOrder()->value('id_agama'),
                'tanggal_lahir' => $faker->dateTimeBetween('-50 years', '-18 years'),
            ]);
        }
    }
}

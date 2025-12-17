<?php

namespace Database\Seeders;

use App\Models\Agama;
use App\Models\Wilayah;
use App\Models\Pekerjaan;
use App\Models\Penghasilan;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use App\Models\JenisTinggal;
use Illuminate\Database\Seeder;
use App\Models\AlatTransportasi;
use App\Models\BiodataMahasiswa;
use App\Models\JenjangPendidikan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create('id_ID');

        for ($i = 1; $i <= 10; $i++) {

            // Wilayah berjenjang
            $provinsi = Wilayah::where('id_level_wilayah', 1)->inRandomOrder()->value('id_wilayah');
            $kabupaten = Wilayah::where('id_level_wilayah', 2)
                ->where('id_induk_wilayah', $provinsi)
                ->inRandomOrder()->value('id_wilayah');

            $kecamatan = Wilayah::where('id_level_wilayah', 3)
                ->where('id_induk_wilayah', $kabupaten)
                ->inRandomOrder()->value('id_wilayah');

            BiodataMahasiswa::create([
                'id_mahasiswa' => Str::uuid()->toString(),
                'nama_lengkap' => $faker->name(),
                'jenis_kelamin' => $faker->randomElement(['L', 'P']),
                'id_agama' => Agama::inRandomOrder()->value('id_agama'),
                'tanggal_lahir' => $faker->date(),
                'tempat_lahir' => $faker->city(),
                'nik' => $faker->numerify('################'),
                'nisn' => $faker->numerify('##########'),
                'npwp' => $faker->numerify('###############'),

                'penerima_kps' => $faker->randomElement(['0', '1']),
                'no_kps' => $faker->numerify('############'),

                'telepone' => $faker->numerify('08##########'),
                'no_hp' => $faker->numerify('08##########'),
                'email' => $faker->unique()->email(),

                // Alamat
                'kewarganegaraan' => 'ID',
                'kelurahan' => $faker->streetName(),
                'jalan' => $faker->streetAddress(),
                'rt' => $faker->numerify('###'),
                'rw' => $faker->numerify('###'),
                'dusun' => $faker->streetName(),
                'kode_pos' => $faker->postcode(),

                // 'id_provinsi' => $provinsi,
                // 'id_kabupaten' => $kabupaten,
                'id_wilayah' => $kecamatan,
                'id_jenis_tinggal' => JenisTinggal::inRandomOrder()->value('id_jenis_tinggal'),
                'id_alat_transportasi' => AlatTransportasi::inRandomOrder()->value('id_alat_transportasi'),

                // Ibu
                'nama_ibu_kandung' => $faker->name('female'),
                'tanggal_lahir_ibu' => $faker->date(),
                'nik_ibu' => $faker->numerify('################'),
                'id_pekerjaan_ibu' => Pekerjaan::inRandomOrder()->value('id_pekerjaan'),
                'id_penghasilan_ibu' => Penghasilan::inRandomOrder()->value('id_penghasilan'),
                'id_pendidikan_ibu' => JenjangPendidikan::inRandomOrder()->value('id_jenjang_didik'),

                // Ayah
                'nama_ayah' => $faker->name('male'),
                'tanggal_lahir_ayah' => $faker->date(),
                'nik_ayah' => $faker->numerify('################'),
                'id_pekerjaan_ayah' => Pekerjaan::inRandomOrder()->value('id_pekerjaan'),
                'id_penghasilan_ayah' => Penghasilan::inRandomOrder()->value('id_penghasilan'),
                'id_pendidikan_ayah' => JenjangPendidikan::inRandomOrder()->value('id_jenjang_didik'),

                // Wali
                'nama_wali' => $faker->name(),
                'nik_wali' => $faker->numerify('################'),
                'id_pekerjaan_wali' => Pekerjaan::inRandomOrder()->value('id_pekerjaan'),
                'id_penghasilan_wali' => Penghasilan::inRandomOrder()->value('id_penghasilan'),
                'id_pendidikan_wali' => JenjangPendidikan::inRandomOrder()->value('id_jenjang_didik'),
            ]);
        }
    }
}

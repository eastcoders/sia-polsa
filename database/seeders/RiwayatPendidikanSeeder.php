<?php

namespace Database\Seeders;

use App\Models\Prodi;
use App\Models\Semester;
use App\Models\ProfilePT;
use App\Models\JalurMasuk;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Models\BiodataMahasiswa;
use App\Models\JenisPendaftaran;
use App\Models\RiwayatPendidikan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RiwayatPendidikanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void   
    {
        // cek jika data mahasiswa kosong
        if (BiodataMahasiswa::count() == 0) {
            $this->call(MahasiswaSeeder::class);
        }

        $faker = Faker::create('id_ID');

        $mahasiswas = BiodataMahasiswa::all();

        foreach ($mahasiswas as $mahasiswa) {
            RiwayatPendidikan::create([
                'id_registrasi_mahasiswa' => Str::uuid()->toString(),
                'nim' => $faker->numerify('##########'),
                'id_mahasiswa' => $mahasiswa->id_mahasiswa,
                'id_biodata_mahasiswa' => $mahasiswa->id,
                'id_prodi' => Prodi::inRandomOrder()->value('id_prodi'),
                'id_jenis_daftar' => '1',
                'id_jalur_daftar' => JalurMasuk::inRandomOrder()->value('id_jalur_masuk'),
                'id_periode_masuk' => Semester::where('id_semester', '20251')->value('id_semester'),
                'tanggal_daftar' => now(),
                'id_pembiayaan' => '1',
                'biaya_masuk' => '200000',
                'id_perguruan_tinggi' => ProfilePT::first()->id_perguruan_tinggi,
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Prodi;
use App\Models\MataKuliah;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;


class MataKuliahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $sks_tatap_muka = $faker->numberBetween(1, 4);
        $sks_praktek = $faker->numberBetween(1, 4);
        $sks_praktek_lapangan = $faker->numberBetween(1, 4);
        $sks_simulasi = $faker->numberBetween(1, 4);

        $sks_mata_kuliah = $sks_tatap_muka + $sks_praktek + $sks_praktek_lapangan + $sks_simulasi;

        $namaMatkul = [
            'Pengantar Teknologi Informasi',
            'Algoritma dan Pemrograman',
            'Struktur Data',
            'Basis Data',
            'Sistem Operasi',
            'Jaringan Komputer',
            'Pemrograman Web',
            'Pemrograman Berorientasi Objek',
            'Rekayasa Perangkat Lunak',
            'Analisis dan Perancangan Sistem',
            'Kecerdasan Buatan',
            'Pembelajaran Mesin',
            'Sistem Informasi Manajemen',
            'Keamanan Informasi',
            'Interaksi Manusia dan Komputer',
            'Manajemen Proyek TI',
            'Komputasi Awan',
            'Internet of Things',
            'Data Mining',
            'Big Data',
            'Metodologi Penelitian',
            'Statistika',
            'Matematika Diskrit',
            'Logika Informatika',
            'Etika Profesi',
            'Kewirausahaan',
            'Bahasa Inggris',
            'Bahasa Indonesia',
            'Pendidikan Pancasila',
            'Kewarganegaraan',
            'Pemrograman Mobile',
            'Grafika Komputer',
            'Sistem Pakar',
            'Teknologi Blockchain',
            'Visualisasi Data',
        ];

        shuffle($namaMatkul);

        foreach ($namaMatkul as $nama) {
            MataKuliah::create([
                'id_matkul' => Str::uuid()->toString(),
                'nama_mata_kuliah' => $nama,
                'kode_mata_kuliah' => 'MK-' . strtoupper(Str::random(5)),
                'id_prodi' => Prodi::inRandomOrder()->value('id_prodi'),
                'sks_mata_kuliah' => $sks_mata_kuliah,
                'sks_tatap_muka' => $sks_tatap_muka,
                'sks_praktek' => $sks_praktek,
                'sks_praktek_lapangan' => $sks_praktek_lapangan,
                'sks_simulasi' => $sks_simulasi,
                'metode_kuliah' => 'Offline/Tatap Muka',
                'id_kelompok_mata_kuliah' => 'A',
                'id_jenis_mata_kuliah' => 'A',
                'tanggal_mulai_efektif' => now(),
                'tanggal_akhir_efektif' => now()->addMonths(6),
            ]);
        }

    }
}

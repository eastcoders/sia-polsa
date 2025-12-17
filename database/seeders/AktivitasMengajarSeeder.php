<?php

namespace Database\Seeders;

use App\Models\KelasKuliah;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use App\Models\PenugasanDosen;
use Illuminate\Database\Seeder;
use App\Models\DosenPengajarKelasKuliah;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AktivitasMengajarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        foreach (KelasKuliah::all() as $kelas) {

            $penugasanDosens = PenugasanDosen::where('id_tahun_ajaran', now()->year)
                ->inRandomOrder()
                ->first();

            DosenPengajarKelasKuliah::create([
                'id_aktivitas_mengajar' => Str::uuid()->toString(),
                'id_kelas_kuliah' => $kelas->id_kelas_kuliah,
                'id_registrasi_dosen' => $penugasanDosens->id_registrasi_dosen,
                'sks_substansi_total' => $faker->numberBetween(1, 4),
                'rencana_minggu_pertemuan' => $faker->numberBetween(14, 16),
                'realisasi_minggu_pertemuan' => 0,
                'id_jenis_evaluasi' => $faker->randomElement(['1', '2', '3', '4']),
                'punya_alias' => '0',
                'id_dosen_alias' => null
            ]);
        }
        // }
    }
}

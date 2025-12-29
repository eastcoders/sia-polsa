<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\DosenPengajarKelasKuliah;
use App\Models\KelasKuliah;
use App\Models\PenugasanDosen;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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

            $dosen_alias = $faker->randomElement(['0', '1']);
            $dosenAlias = null;

            if ($dosen_alias == '1') {
                $dosenAlias = Dosen::where('nip', null)
                    ->where('nidn', null)
                    ->inRandomOrder()
                    ->first();
            }

            DosenPengajarKelasKuliah::create([
                'id_aktivitas_mengajar' => Str::uuid()->toString(),
                'id_kelas_kuliah' => $kelas->id_kelas_kuliah,
                'id_registrasi_dosen' => $penugasanDosens->id_registrasi_dosen,
                'sks_substansi_total' => $faker->numberBetween(1, 4),
                'rencana_minggu_pertemuan' => $faker->numberBetween(14, 16),
                'realisasi_minggu_pertemuan' => 0,
                'id_jenis_evaluasi' => $faker->randomElement(['1', '2', '3', '4']),
                'punya_alias' => $dosen_alias,
                'id_dosen_alias' => $dosenAlias->id_dosen ?? null,
            ]);
        }
        // }
    }
}

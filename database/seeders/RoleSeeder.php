<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'admin',
            'dosen',
            'kaprodi',
            'direktur',
            'dosen_pengajar',
            'dosen_pembina_akademik',
            'mahasiswa',
            'orang_tua',
            'keuangan',
            'wadir',
            'kemahasiswaan',
            'bpmi',
            'perpustakaan',
            'kepegawaian',
            'sarpras',
            'personalia',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}

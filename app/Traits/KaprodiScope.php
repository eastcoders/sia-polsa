<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait KaprodiScope
{
    /**
     * Memodifikasi query standar Eloquent untuk membatasi data sesuai hak akses Kaprodi.
     * Fungsi ini akan otomatis dipanggil oleh Filament setiap kali Resource menampilkan tabel.
     */
    public static function getEloquentQuery(): Builder
    {
        // 1. Ambil query standar dari parent (bawaan Filament/Laravel)
        $query = parent::getEloquentQuery();

        // 2. Ambil User yang sedang login
        $user = Auth::user();

        /**
         * Cek apakah User ini memiliki role 'kaprodi'.
         * Kita asumsikan role 'kaprodi' sudah diset di Spatie Permission.
         * Jika belum ada role khusus, logika ini bisa disesuaikan, 
         * misalnya cek apakah dia punya data 'dosen' dan 'memimpinProdi'.
         */
        if ($user && $user->hasRole('kaprodi')) {

            // 3. Pastikan User ini terhubung dengan data Dosen
            if ($user->dosen) {

                // 4. Ambil daftar ID Prodi yang dipimpin oleh Dosen ini.
                // Kita ambil kolom 'id_prodi' karena tabel lain berelasi pakai kolom ini.
                $prodiIds = $user->dosen->memimpinProdi()->pluck('id_prodi')->toArray();

                // 5. Terapkan Filter Global (Global Scope)
                if (!empty($prodiIds)) {
                    $model = $query->getModel();

                    // Cek jika model adalah AktivitasKuliahMahasiswa (yang tidak punya kolom id_prodi langsung)
                    if ($model instanceof \App\Models\AktivitasKuliahMahasiswa) {
                        $query->whereHas('riwayatPendidikan', function ($q) use ($prodiIds) {
                            $q->whereIn('id_prodi', $prodiIds);
                        });
                    } else {
                        // Default: Asumsikan tabel punya kolom 'id_prodi' (seperti KelasKuliah, RiwayatPendidikan)
                        $query->whereIn('id_prodi', $prodiIds);
                    }
                } else {
                    // EDGE CASE: Jika dia user Kaprodi tapi datanya belum diset memimpin prodi apapun.
                    $query->whereRaw('1 = 0');
                }
            }
        }

        // 6. Kembalikan query yang sudah dimodifikasi (atau original jika bukan kaprodi)
        return $query;
    }
}

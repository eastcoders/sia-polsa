<?php

namespace App\Observers;

use App\Models\NilaiKelasPerkuliahan;
use App\Models\AktivitasKuliahMahasiswa;
use Illuminate\Validation\ValidationException;

class NilaiKelasPerkuliahanObserver
{
    /**
     * Handle the NilaiKelasPerkuliahan "updating" event.
     */
    public function updating(NilaiKelasPerkuliahan $nilaiKelasPerkuliahan): void
    {
        // 1. Load relasi minimal untuk mendapatkan ID Semester & ID Mahasiswa
        $nilaiKelasPerkuliahan->loadMissing(['kelasKuliah']);

        $idSemester = $nilaiKelasPerkuliahan->kelasKuliah?->id_semester;
        $idRegistrasiMahasiswa = $nilaiKelasPerkuliahan->id_registrasi_mahasiswa;

        if ($idSemester && $idRegistrasiMahasiswa) {
            // 2. Cari data Aktivitas Kuliah Mahasiswa (AKM) yang sesuai
            $akm = AktivitasKuliahMahasiswa::where('id_semester', $idSemester)
                ->where('id_registrasi_mahasiswa', $idRegistrasiMahasiswa)
                ->first();

            // 3. Cek Approval Status
            if ($akm && $akm->khs_is_approved) {
                throw ValidationException::withMessages([
                    'nilai' => 'Nilai tidak dapat diubah karena KHS mahasiswa ini sudah di-ACC oleh Kaprodi untuk semester ini.',
                ]);
            }
        }
    }

    /**
     * Handle the NilaiKelasPerkuliahan "deleting" event.
     */
    public function deleting(NilaiKelasPerkuliahan $nilaiKelasPerkuliahan): void
    {
        $nilaiKelasPerkuliahan->loadMissing(['kelasKuliah']);

        $idSemester = $nilaiKelasPerkuliahan->kelasKuliah?->id_semester;
        $idRegistrasiMahasiswa = $nilaiKelasPerkuliahan->id_registrasi_mahasiswa;

        if ($idSemester && $idRegistrasiMahasiswa) {
            $akm = AktivitasKuliahMahasiswa::where('id_semester', $idSemester)
                ->where('id_registrasi_mahasiswa', $idRegistrasiMahasiswa)
                ->first();

            if ($akm && $akm->khs_is_approved) {
                throw ValidationException::withMessages([
                    'nilai' => 'Nilai tidak dapat dihapus karena KHS mahasiswa ini sudah di-ACC oleh Kaprodi untuk semester ini.',
                ]);
            }
        }
    }
}

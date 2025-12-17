<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesertaKelasKuliah extends Model
{
    public function riwayatPendidikan()
    {
        return $this->hasOne(RiwayatPendidikan::class, 'id_registrasi_mahasiswa', 'id_registrasi_mahasiswa');
    }

    public function kelasKuliah()
    {
        return $this->belongsTo(KelasKuliah::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }

    public function nilaiKelasPerkuliahan()
    {
        return $this->belongsTo(NilaiKelasPerkuliahan::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }

    public function nilaiKuliah()
    {
        return $this->belongsTo(NilaiKelasPerkuliahan::class, 'id_registrasi_mahasiswa', 'id_registrasi_mahasiswa');
    }

    public function skalaNilai()
    {
        return $this->belongsTo(SkalaNilai::class, 'id_prodi');
    }

    public function getIdProdiAttribute()
    {
        return $this->kelasKuliah?->id_prodi;
    }

    public function skalaNilaiOptions()
    {
        return SkalaNilai::where('id_prodi', $this->id_prodi)
            ->orderBy('nilai_huruf')
            ->get();
    }
}

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
        return $this->hasMany(KelasKuliah::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }

    public function nilaiKelasPerkuliahan()
    {
        return $this->belongsTo(NilaiKelasPerkuliahan::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }
}

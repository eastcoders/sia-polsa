<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiwayatPendidikan extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    }

    public function jenisPendaftaran()
    {
        return $this->belongsTo(JenisPendaftaran::class, 'id_jenis_daftar', 'id_jenis_daftar');
    }

    public function jalurMasuk()
    {
        return $this->belongsTo(JalurMasuk::class, 'id_jalur_daftar', 'id_jalur_masuk');
    }

    public function periodeDaftar()
    {
        return $this->belongsTo(Semester::class, 'id_periode_masuk', 'id_semester');
    }

    public function mahasiswa()
    {
        return $this->hasOne(BiodataMahasiswa::class, 'id_mahasiswa', 'id_mahasiswa');
    }

    public function nilaiKuliah()
    {
        return $this->belongsTo(NilaiKelasPerkuliahan::class, 'id_registrasi_mahasiswa', 'id_registrasi_mahasiswa');
    }
}

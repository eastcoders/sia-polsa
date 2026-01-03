<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiMahasiswa extends Model
{
    protected $fillable = [
        'id_pertemuan_kelas',
        'id_registrasi_mahasiswa',
        'status_kehadiran',
        'keterangan',
    ];

    public function pertemuanKelas()
    {
        return $this->belongsTo(PertemuanKelas::class, 'id_pertemuan_kelas');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(PesertaKelasKuliah::class, 'id_registrasi_mahasiswa', 'id_registrasi_mahasiswa');
    }
}

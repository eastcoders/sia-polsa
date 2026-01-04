<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NilaiEvaluasiAkhir extends Model
{
    protected $table = 'nilai_evaluasi_akhir';

    protected $fillable = [
        'id_kelas_kuliah',
        'id_registrasi_mahasiswa',
        'jenis_nilai',
        'nilai',
    ];

    public function kelasKuliah()
    {
        return $this->belongsTo(KelasKuliah::class, 'id_kelas_kuliah');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(PesertaKelasKuliah::class, 'id_registrasi_mahasiswa', 'id_registrasi_mahasiswa');
    }
}

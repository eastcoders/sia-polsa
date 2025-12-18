<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NilaiKelasPerkuliahan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id_kelas_kuliah',
        'id_registrasi_mahasiswa',
        'nilai_angka',
        'nilai_huruf',
        'nilai_indeks',
        'sync_at',
    ];

    public function kelasKuliah()
    {
        return $this->belongsTo(KelasKuliah::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }
}

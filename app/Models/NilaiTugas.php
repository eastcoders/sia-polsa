<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NilaiTugas extends Model
{
    protected $table = 'nilai_tugas';

    protected $fillable = [
        'id_tugas_pertemuan',
        'id_registrasi_mahasiswa',
        'nilai',
        'feedback',
    ];

    public function tugasPertemuan()
    {
        return $this->belongsTo(TugasPertemuan::class, 'id_tugas_pertemuan');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(PesertaKelasKuliah::class, 'id_registrasi_mahasiswa', 'id_registrasi_mahasiswa');
    }
}

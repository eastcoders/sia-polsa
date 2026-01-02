<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenugasanDosen extends Model
{
    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen', 'id_dosen');
    }

    public function dosenPengajarKelasKuliahs()
    {
        return $this->hasMany(DosenPengajarKelasKuliah::class, 'id_registrasi_dosen', 'id_registrasi_dosen');
    }
}

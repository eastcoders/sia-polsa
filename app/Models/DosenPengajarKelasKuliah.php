<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DosenPengajarKelasKuliah extends Model
{
    public function dosenAlias()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen_alias', 'id_dosen');
    }

    public function registrasiDosen()
    {
        return $this->belongsTo(PenugasanDosen::class, 'id_registrasi_dosen', 'id_registrasi_dosen');
    }
}

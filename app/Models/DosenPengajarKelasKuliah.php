<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DosenPengajarKelasKuliah extends Model
{
    use SoftDeletes;
    public function dosenAlias()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen_alias', 'id_dosen');
    }

    public function registrasiDosen()
    {
        return $this->belongsTo(PenugasanDosen::class, 'id_registrasi_dosen', 'id_registrasi_dosen');
    }

    public function kelasKuliah()
    {
        return $this->belongsTo(KelasKuliah::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }
}

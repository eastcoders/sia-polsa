<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KelasKuliah extends Model
{
    use SoftDeletes;

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }

    public function matkul()
    {
        return $this->belongsTo(MataKuliah::class, 'id_matkul', 'id_matkul');
    }

    public function pesertaKelas()
    {
        return $this->belongsTo(PesertaKelasKuliah::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }
}

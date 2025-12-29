<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalPerkuliahan extends Model
{
    public function semester()
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }

    public function ruangKelas()
    {
        return $this->belongsTo(RuangKelas::class, 'id_ruang', 'id');
    }

    public function kelasKuliah()
    {
        return $this->belongsTo(KelasKuliah::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }
}

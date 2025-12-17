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

    public function dosenPengajarKelasKuliah()
    {
        return $this->hasMany(DosenPengajarKelasKuliah::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }

    protected static function booted()
    {
        static::deleting(function ($kelas) {
            $kelas->dosenPengajarKelasKuliah()->delete();
            $kelas->nilaiKuliah()->delete();
        });
    }

    public function nilaiKuliah()
    {
        return $this->belongsTo(NilaiKelasPerkuliahan::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }
}

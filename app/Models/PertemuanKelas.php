<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PertemuanKelas extends Model
{
    protected $table = 'pertemuan_kelas';

    protected $guarded = [];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function kelasKuliah()
    {
        return $this->belongsTo(KelasKuliah::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }

    public function jadwalPerkuliahan()
    {
        return $this->belongsTo(JadwalPerkuliahan::class, 'id_jadwal_perkuliahan', 'id');
    }

    public function presensiMahasiswas()
    {
        return $this->hasMany(PresensiMahasiswa::class, 'id_pertemuan_kelas', 'id');
    }

    public function tugasPertemuan()
    {
        return $this->hasOne(TugasPertemuan::class, 'id_pertemuan_kelas');
    }
}

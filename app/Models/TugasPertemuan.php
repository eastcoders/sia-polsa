<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TugasPertemuan extends Model
{
    protected $table = 'tugas_pertemuan';

    protected $fillable = [
        'id_pertemuan_kelas',
        'judul_tugas',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function pertemuanKelas()
    {
        return $this->belongsTo(PertemuanKelas::class, 'id_pertemuan_kelas');
    }

    public function nilaiTugas()
    {
        return $this->hasMany(NilaiTugas::class, 'id_tugas_pertemuan');
    }
}

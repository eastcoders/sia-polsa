<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalUjian extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tanggal_ujian' => 'date',
        'deadline_submission' => 'datetime',
        'is_published' => 'boolean',
        'jam_mulai' => 'datetime',
        'jam_selesai' => 'datetime',
    ];

    public function kelasKuliah()
    {
        return $this->belongsTo(KelasKuliah::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }

    public function ruangKelas()
    {
        return $this->belongsTo(RuangKelas::class, 'id_ruang', 'id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}


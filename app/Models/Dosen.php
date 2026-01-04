<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    public function agama()
    {
        return $this->belongsTo(Agama::class, 'id_agama', 'id_agama');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'dosen_id');
    }

    public function penugasanDosen()
    {
        return $this->belongsTo(PenugasanDosen::class, 'id_dosen', 'id_dosen');
    }

    public function memimpinProdi()
    {
        return $this->hasMany(Prodi::class, 'ketua_prodi_id');
    }

    public function pembinaProdi()
    {
        return $this->hasMany(DosenPembinaProdi::class, 'dosen_id');
    }
}

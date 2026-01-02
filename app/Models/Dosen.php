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
}

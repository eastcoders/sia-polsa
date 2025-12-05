<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    public function agama()
    {
        return $this->belongsTo(Agama::class, 'id_agama');
    }
}

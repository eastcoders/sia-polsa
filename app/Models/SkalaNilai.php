<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkalaNilai extends Model
{
    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    }
}

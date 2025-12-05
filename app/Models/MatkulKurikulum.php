<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatkulKurikulum extends Model
{
    public function matkul()
    {
        return $this->belongsTo(MataKuliah::class, 'id_matkul', 'id_matkul');
    }
}

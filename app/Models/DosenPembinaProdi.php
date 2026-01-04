<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DosenPembinaProdi extends Model
{
    protected $table = 'dosen_pembina_prodis';

    protected $fillable = [
        'dosen_id',
        'prodi_id',
    ];

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }
}

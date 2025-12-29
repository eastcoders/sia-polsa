<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BiodataMahasiswa extends Model
{
    use SoftDeletes;

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_lahir_ayah' => 'date',
        'tanggal_lahir_ibu' => 'date',
        'id_wilayah' => 'string',
    ];

    public function riwayatPendidikan()
    {
        return $this->hasOne(RiwayatPendidikan::class, 'id_mahasiswa', 'id_mahasiswa');
    }

    public function agama()
    {
        return $this->belongsTo(Agama::class, 'id_agama', 'id_agama');
    }
}

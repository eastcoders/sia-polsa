<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KomponenBobotKelas extends Model
{
    protected $table = 'komponen_bobot_kelas';

    protected $fillable = [
        'id_kelas_kuliah',
        'nama_komponen',
        'bobot',
    ];

    public function kelasKuliah()
    {
        return $this->belongsTo(KelasKuliah::class, 'id_kelas_kuliah');
    }
}

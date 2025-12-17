<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kurikulum extends Model
{
    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }

    public function matkulKurikulum(){
        return $this->hasMany(MatkulKurikulum::class, 'id_kurikulum', 'id_kurikulum');
    }

    protected static function booted()
    {
        static::deleting(function ($kurikulum) {
            $kurikulum->matkulKurikulum()->delete();
        });
    }
}

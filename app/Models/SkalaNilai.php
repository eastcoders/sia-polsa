<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class SkalaNilai extends Model
{
    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    }

    protected function skalaIndex(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->nilai_huruf.' ('.$this->nilai_indeks.')',
        );
    }
}

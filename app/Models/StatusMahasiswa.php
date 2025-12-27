<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusMahasiswa extends Model
{
    protected $primaryKey = 'id_status_mahasiswa';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pegawai extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'nip',
        'nama_lengkap',
        'email',
        'no_hp',
        'alamat',
        'jenis_kelamin',
        'jabatan_fungsional',
        'unit_kerja',
        'is_active',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'pegawai_id');
    }
}

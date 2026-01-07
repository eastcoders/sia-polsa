<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AktivitasKuliahMahasiswa extends Model
{
    protected $guarded = [];

    protected $casts = [
        'ipk' => 'float',
        'ips' => 'float',
        'khs_is_approved' => 'boolean',
        'khs_approved_at' => 'datetime',
    ];

    public function approver()
    {
        return $this->belongsTo(User::class, 'khs_approved_by');
    }

    public function riwayatPendidikan()
    {
        return $this->belongsTo(RiwayatPendidikan::class, 'id_registrasi_mahasiswa', 'id_registrasi_mahasiswa');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }

    public function statusMahasiswa()
    {
        return $this->belongsTo(StatusMahasiswa::class, 'id_status_mahasiswa', 'id_status_mahasiswa');
    }

    public function pembiayaan()
    {
        return $this->belongsTo(Pembiayaan::class, 'id_pembiayaan', 'id_pembiayaan');
    }
}

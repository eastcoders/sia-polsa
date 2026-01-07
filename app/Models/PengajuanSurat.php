<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengajuanSurat extends Model
{
    use HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(BiodataMahasiswa::class, 'biodata_mahasiswa_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope untuk Kaprodi: hanya tampilkan surat dari prodi yang dipimpin.
     * Menggunakan relasi: Pengajuan -> Mahasiswa -> RiwayatPendidikan -> Prodi
     */
    public function scopeForKaprodi(Builder $query, array $prodiIds)
    {
        return $query->whereHas('mahasiswa.riwayatPendidikan', function ($q) use ($prodiIds) {
            $q->whereIn('id_prodi', $prodiIds);
        });
    }
}

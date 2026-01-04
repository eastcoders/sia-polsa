<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiwayatPendidikan extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    }

    public function jenisPendaftaran()
    {
        return $this->belongsTo(JenisPendaftaran::class, 'id_jenis_daftar', 'id_jenis_daftar');
    }

    public function jalurMasuk()
    {
        return $this->belongsTo(JalurMasuk::class, 'id_jalur_daftar', 'id_jalur_masuk');
    }

    public function periodeDaftar()
    {
        return $this->belongsTo(Semester::class, 'id_periode_masuk', 'id_semester');
    }

    public function mahasiswa()
    {
        return $this->hasOne(BiodataMahasiswa::class, 'id_mahasiswa', 'id_mahasiswa');
    }

    public function nilaiKuliah()
    {
        return $this->belongsTo(NilaiKelasPerkuliahan::class, 'id_registrasi_mahasiswa', 'id_registrasi_mahasiswa');
    }

    /**
     * Get the student's shift (Pagi/Sore) based on NIM.
     * Logic: Character at index 4 of NIM (1 = Pagi, 2 = Sore).
     */
    protected function waktuKuliah(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function ($value, $attributes) {
                $nim = $attributes['nim'] ?? null;

                if (! $nim || strlen($nim) <= 4) {
                    return 'Tidak Diketahui';
                }

                $kode = substr($nim, 4, 1);

                return match ($kode) {
                    '1' => 'Pagi',
                    '2' => 'Sore',
                    default => 'Tidak Diketahui',
                };
            }
        );
    }
}

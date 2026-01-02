<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiMahasiswa extends Model
{
    protected $fillable = [
        'id_pertemuan_kelas',
        'id_registrasi_mahasiswa',
        'status_kehadiran',
        'keterangan',
    ];

    public function pertemuanKelas()
    {
        return $this->belongsTo(PertemuanKelas::class, 'id_pertemuan_kelas');
    }

    public function mahasiswa()
    {
        // Assuming id_registrasi_mahasiswa links to a Student or Registration model. 
        // Adjusting based on file listing: 'BiodataMahasiswa' or similar logic usually applies.
        // Based on previous code, likely links to something holding 'id_registrasi_mahasiswa'
        // For now, I will link to BiodataMahasiswa assuming it holds the profile, 
        // but typically 'id_registrasi_mahasiswa' implies 'RiwayatPendidikan' or 'PesertaKelasKuliah'.
        // Let's assume it links to BiodataMahasiswa via id_mahasiswa? No, id_registrasi is usually unique per prodi.

        // Let's check a similar model PesertaKelasKuliah to match relationships.
        // PesertaKelasKuliah usually has 'id_registrasi_mahasiswa'.
        return $this->belongsTo(PesertaKelasKuliah::class, 'id_registrasi_mahasiswa', 'id_registrasi_mahasiswa');
    }
}

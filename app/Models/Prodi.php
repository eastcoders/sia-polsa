<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_prodi
 * @property string $nama_program_studi
 * @property string $kode_program_studi
 * @property string $status
 * @property string $id_jenjang_pendidikan
 * @property string $nama_jenjang_pendidikan
 * @property string|null $sync_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prodi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prodi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prodi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prodi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prodi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prodi whereIdJenjangPendidikan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prodi whereIdProdi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prodi whereKodeProgramStudi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prodi whereNamaJenjangPendidikan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prodi whereNamaProgramStudi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prodi whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prodi whereSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prodi whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Prodi extends Model
{
    protected function programStudiLengkap(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->nama_jenjang_pendidikan . ' - ' . $this->nama_program_studi,
        );
    }

    public function ketuaProdi()
    {
        return $this->belongsTo(Dosen::class, 'ketua_prodi_id');
    }

    public function dosenPembina()
    {
        return $this->hasMany(DosenPembinaProdi::class, 'prodi_id');
    }
}

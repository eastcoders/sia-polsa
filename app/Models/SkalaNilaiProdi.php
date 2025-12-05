<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_skala_nilai
 * @property string $id_prodi
 * @property string $nilai_huruf
 * @property string|null $nilai_indeks
 * @property string $bobot_minimum
 * @property string $bobot_maksimum
 * @property \Illuminate\Support\Carbon $tanggal_mulai_efektif
 * @property \Illuminate\Support\Carbon $tanggal_akhir_efektif
 * @property \Illuminate\Support\Carbon|null $sync_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkalaNilaiProdi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkalaNilaiProdi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkalaNilaiProdi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkalaNilaiProdi whereBobotMaksimum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkalaNilaiProdi whereBobotMinimum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkalaNilaiProdi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkalaNilaiProdi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkalaNilaiProdi whereIdProdi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkalaNilaiProdi whereIdSkalaNilai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkalaNilaiProdi whereNilaiHuruf($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkalaNilaiProdi whereNilaiIndeks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkalaNilaiProdi whereSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkalaNilaiProdi whereTanggalAkhirEfektif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkalaNilaiProdi whereTanggalMulaiEfektif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkalaNilaiProdi whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class SkalaNilaiProdi extends Model
{
    protected $casts = [
        'sync_at' => 'datetime',
        'tanggal_mulai_efektif' => 'date',
        'tanggal_akhir_efektif' => 'date',
    ];
}

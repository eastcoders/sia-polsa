<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_semester
 * @property string $nama_semester
 * @property string $id_tahun_ajaran
 * @property string $semester
 * @property string $a_periode_aktif
 * @property \Illuminate\Support\Carbon $tanggal_mulai
 * @property \Illuminate\Support\Carbon $tanggal_selesai
 * @property \Illuminate\Support\Carbon|null $sync_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereAPeriodeAktif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereIdSemester($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereIdTahunAjaran($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereNamaSemester($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereSemester($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereTanggalMulai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereTanggalSelesai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Semester extends Model
{
    protected $casts = [
        'sync_at' => 'datetime',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];
}

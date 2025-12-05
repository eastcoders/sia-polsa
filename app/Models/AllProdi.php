<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_prodi
 * @property string $id_perguruan_tinggi
 * @property string $nama_perguruan_tinggi
 * @property string $nama_program_studi
 * @property string $kode_program_studi
 * @property string $status
 * @property string $id_jenjang_pendidikan
 * @property string $nama_jenjang_pendidikan
 * @property string|null $sync_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AllProdi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AllProdi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AllProdi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AllProdi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AllProdi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AllProdi whereIdJenjangPendidikan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AllProdi whereIdPerguruanTinggi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AllProdi whereIdProdi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AllProdi whereKodeProgramStudi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AllProdi whereNamaJenjangPendidikan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AllProdi whereNamaPerguruanTinggi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AllProdi whereNamaProgramStudi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AllProdi whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AllProdi whereSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AllProdi whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class AllProdi extends Model
{
    //
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_perguruan_tinggi
 * @property string $nama_perguruan_tinggi
 * @property string $kode_perguruan_tinggi
 * @property string $nama_singkat
 * @property string|null $sync_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerguruanTinggi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerguruanTinggi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerguruanTinggi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerguruanTinggi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerguruanTinggi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerguruanTinggi whereIdPerguruanTinggi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerguruanTinggi whereKodePerguruanTinggi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerguruanTinggi whereNamaPerguruanTinggi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerguruanTinggi whereNamaSingkat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerguruanTinggi whereSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerguruanTinggi whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PerguruanTinggi extends Model
{
    //
}

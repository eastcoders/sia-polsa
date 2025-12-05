<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_negara
 * @property string $id_wilayah
 * @property string $nama_wilayah
 * @property string $id_level_wilayah
 * @property string $id_induk_wilayah
 * @property \Illuminate\Support\Carbon|null $sync_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah whereIdIndukWilayah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah whereIdLevelWilayah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah whereIdNegara($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah whereIdWilayah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah whereNamaWilayah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah whereSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Wilayah extends Model
{
    protected $casts = [
        'sync_at' => 'datetime',
    ];
}

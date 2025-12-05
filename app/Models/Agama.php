<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_agama
 * @property string $nama_agama
 * @property \Illuminate\Support\Carbon|null $sync_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agama newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agama newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agama query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agama whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agama whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agama whereIdAgama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agama whereNamaAgama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agama whereSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agama whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Agama extends Model
{
    protected $casts = [
        'sync_at' => 'datetime',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_negara
 * @property string $nama_negara
 * @property \Illuminate\Support\Carbon|null $sync_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Negara newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Negara newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Negara query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Negara whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Negara whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Negara whereIdNegara($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Negara whereNamaNegara($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Negara whereSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Negara whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Negara extends Model
{
    protected $casts = [
        'sync_at' => 'datetime',
    ];
}

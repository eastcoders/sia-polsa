<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_penghasilan
 * @property string $nama_penghasilan
 * @property \Illuminate\Support\Carbon|null $sync_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penghasilan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penghasilan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penghasilan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penghasilan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penghasilan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penghasilan whereIdPenghasilan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penghasilan whereNamaPenghasilan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penghasilan whereSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penghasilan whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Penghasilan extends Model
{
    protected $casts = [
        'sync_at' => 'datetime',
    ];
}

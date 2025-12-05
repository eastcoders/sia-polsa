<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_pekerjaan
 * @property string $nama_pekerjaan
 * @property \Illuminate\Support\Carbon|null $sync_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pekerjaan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pekerjaan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pekerjaan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pekerjaan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pekerjaan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pekerjaan whereIdPekerjaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pekerjaan whereNamaPekerjaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pekerjaan whereSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pekerjaan whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Pekerjaan extends Model
{
    protected $casts = [
        'sync_at' => 'datetime',
    ];
}

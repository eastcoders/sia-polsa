<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_jenis_tinggal
 * @property string $nama_jenis_tinggal
 * @property \Illuminate\Support\Carbon|null $sync_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisTinggal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisTinggal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisTinggal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisTinggal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisTinggal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisTinggal whereIdJenisTinggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisTinggal whereNamaJenisTinggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisTinggal whereSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisTinggal whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class JenisTinggal extends Model
{
    protected $casts = [
        'sync_at' => 'datetime',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_kebutuhan_khusus
 * @property string $nama_kebutuhan_khusus
 * @property string|null $sync_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KebutuhanKhusus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KebutuhanKhusus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KebutuhanKhusus query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KebutuhanKhusus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KebutuhanKhusus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KebutuhanKhusus whereIdKebutuhanKhusus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KebutuhanKhusus whereNamaKebutuhanKhusus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KebutuhanKhusus whereSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KebutuhanKhusus whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class KebutuhanKhusus extends Model
{
    protected $casts = [
        'sync_at' => 'datetime',
    ];
}

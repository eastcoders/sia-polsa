<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_alat_transportasi
 * @property string $nama_alat_transportasi
 * @property \Illuminate\Support\Carbon|null $sync_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlatTransportasi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlatTransportasi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlatTransportasi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlatTransportasi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlatTransportasi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlatTransportasi whereIdAlatTransportasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlatTransportasi whereNamaAlatTransportasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlatTransportasi whereSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlatTransportasi whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class AlatTransportasi extends Model
{
    protected $casts = [
        'sync_at' => 'datetime',
    ];
}

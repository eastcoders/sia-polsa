<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_jalur_masuk
 * @property string $nama_jalur_masuk
 * @property \Illuminate\Support\Carbon|null $sync_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JalurMasuk newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JalurMasuk newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JalurMasuk query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JalurMasuk whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JalurMasuk whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JalurMasuk whereIdJalurMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JalurMasuk whereNamaJalurMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JalurMasuk whereSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JalurMasuk whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class JalurMasuk extends Model
{
    protected $casts = [
        'sync_at' => 'datetime',
    ];
}

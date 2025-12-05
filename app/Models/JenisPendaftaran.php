<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_jenis_daftar
 * @property string $nama_jenis_daftar
 * @property \Illuminate\Support\Carbon|null $sync_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisPendaftaran newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisPendaftaran newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisPendaftaran query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisPendaftaran whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisPendaftaran whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisPendaftaran whereIdJenisDaftar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisPendaftaran whereNamaJenisDaftar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisPendaftaran whereSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenisPendaftaran whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class JenisPendaftaran extends Model
{
    protected $casts = [
        'sync_at' => 'datetime',
    ];
}

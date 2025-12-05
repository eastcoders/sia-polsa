<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_jenjang_didik
 * @property string $nama_jenjang_didik
 * @property \Illuminate\Support\Carbon|null $sync_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenjangPendidikan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenjangPendidikan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenjangPendidikan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenjangPendidikan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenjangPendidikan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenjangPendidikan whereIdJenjangDidik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenjangPendidikan whereNamaJenjangDidik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenjangPendidikan whereSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JenjangPendidikan whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class JenjangPendidikan extends Model
{
    protected $table = 'jenjang_pendidikans';

    protected $casts = [
        'sync_at' => 'datetime',
    ];
}

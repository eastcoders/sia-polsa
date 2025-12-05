<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_pembiayaan
 * @property string $nama_pembiayaan
 * @property string|null $sync_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembiayaan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembiayaan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembiayaan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembiayaan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembiayaan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembiayaan whereIdPembiayaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembiayaan whereNamaPembiayaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembiayaan whereSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembiayaan whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Pembiayaan extends Model
{
    //
}

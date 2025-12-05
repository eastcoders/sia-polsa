<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_bidang_minat
 * @property string $nm_bidang_minat
 * @property string $id_prodi
 * @property string $nama_program_studi
 * @property string $smt_dimulai
 * @property string $tamat_sk_bidang_minat
 * @property string|null $sync_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BidangMinat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BidangMinat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BidangMinat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BidangMinat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BidangMinat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BidangMinat whereIdBidangMinat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BidangMinat whereIdProdi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BidangMinat whereNamaProgramStudi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BidangMinat whereNmBidangMinat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BidangMinat whereSmtDimulai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BidangMinat whereSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BidangMinat whereTamatSkBidangMinat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BidangMinat whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class BidangMinat extends Model
{
    //
}

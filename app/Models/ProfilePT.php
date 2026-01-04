<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property string $id_perguruan_tinggi
 * @property string $kode_perguruan_tinggi
 * @property string $nama_perguruan_tinggi
 * @property string $telepon
 * @property string $faximile
 * @property string $email
 * @property string $website
 * @property string $jalan
 * @property string $dusun
 * @property string $rt_rw
 * @property string $kelurahan
 * @property string $kode_pos
 * @property string $id_wilayah
 * @property string|null $sync_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfilePT newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfilePT newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfilePT query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfilePT whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfilePT whereDusun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfilePT whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfilePT whereFaximile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfilePT whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfilePT whereIdPerguruanTinggi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfilePT whereIdWilayah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfilePT whereJalan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfilePT whereKelurahan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfilePT whereKodePerguruanTinggi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfilePT whereKodePos($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfilePT whereNamaPerguruanTinggi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfilePT whereRtRw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfilePT whereSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfilePT whereTelepon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfilePT whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfilePT whereWebsite($value)
 *
 * @mixin \Eloquent
 */
class ProfilePT extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->useDisk('public');
    }

    public function direktur()
    {
        return $this->belongsTo(\App\Models\Dosen::class, 'direktur_id');
    }

    public function wadir1()
    {
        return $this->belongsTo(\App\Models\Dosen::class, 'wadir1_id');
    }

    public function wadir2()
    {
        return $this->belongsTo(\App\Models\Dosen::class, 'wadir2_id');
    }

    public function wadir3()
    {
        return $this->belongsTo(\App\Models\Dosen::class, 'wadir3_id');
    }
}

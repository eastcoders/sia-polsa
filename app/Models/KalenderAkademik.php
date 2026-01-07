<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KalenderAkademik extends Model
{
    protected $fillable = [
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'is_libur',
        'is_minggu_ujian',
        'id_semester',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_libur' => 'boolean',
        'is_minggu_ujian' => 'boolean',
    ];

    public function scopeActive($query, $date = null)
    {
        $date = $date ?? now();
        return $query->whereDate('tanggal_mulai', '<=', $date)
            ->whereDate('tanggal_selesai', '>=', $date);
    }

    public function scopeHolidays($query)
    {
        return $query->where('is_libur', true);
    }

    public function scopeExamWeeks($query)
    {
        return $query->where('is_minggu_ujian', true);
    }
}

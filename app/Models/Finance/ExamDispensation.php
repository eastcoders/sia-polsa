<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamDispensation extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_registrasi_mahasiswa',
        'type', // UTS, UAS, KRS
        'valid_until',
        'reason',
        'approved_by',
    ];

    protected $casts = [
        'valid_until' => 'date',
    ];

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function scopeActive($query)
    {
        return $query->where('valid_until', '>=', now());
    }
}

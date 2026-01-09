<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFeeAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_registrasi_mahasiswa',
        'fee_structure_id',
        'valid_from',
        'valid_to',
        'is_active',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    // Scope to get valid assignment for a specific date
    public function scopeActiveOn($query, $date)
    {
        return $query->where('is_active', true)
            ->where('valid_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $date);
            });
    }
}

<?php

declare(strict_types=1);

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scholarship extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'funding_source',
        'coverage_percentage',
        'description',
        'is_active',
    ];

    protected $casts = [
        'coverage_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ===== RELATIONSHIPS =====

    /**
     * Get all student assignments for this scholarship
     */
    public function studentScholarships(): HasMany
    {
        return $this->hasMany(StudentScholarship::class);
    }

    // ===== SCOPES =====

    /**
     * Scope to only active scholarships
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by funding source
     */
    public function scopeByFundingSource($query, string $source)
    {
        return $query->where('funding_source', $source);
    }

    // ===== ACCESSORS =====

    /**
     * Get formatted coverage percentage
     */
    public function getFormattedCoverageAttribute(): string
    {
        return number_format((float) $this->coverage_percentage, 0) . '%';
    }

    /**
     * Get funding source label (Indonesian)
     */
    public function getFundingSourceLabelAttribute(): string
    {
        return match ($this->funding_source) {
            'GOVERNMENT' => 'Pemerintah',
            'FOUNDATION' => 'Yayasan',
            'INSTITUTION' => 'Institusi',
            'CORPORATE' => 'Perusahaan/Sponsor',
            default => $this->funding_source,
        };
    }
}

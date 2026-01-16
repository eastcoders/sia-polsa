<?php

declare(strict_types=1);

namespace App\Models\Finance;

use App\Enums\Finance\ScholarshipStatus;
use App\Models\RiwayatPendidikan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class StudentScholarship extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_registrasi_mahasiswa',
        'scholarship_id',
        'valid_from',
        'valid_until',
        'coverage_type',
        'status',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'approved_at' => 'datetime',
        'status' => ScholarshipStatus::class,
    ];

    // ===== RELATIONSHIPS =====

    /**
     * Get the scholarship master data
     */
    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    /**
     * Get the student's registration record
     */
    public function riwayatPendidikan(): BelongsTo
    {
        return $this->belongsTo(RiwayatPendidikan::class, 'id_registrasi_mahasiswa', 'id_registrasi_mahasiswa');
    }

    /**
     * Get the approving user
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get invoices covered by this scholarship assignment
     */
    public function coveredInvoices(): HasMany
    {
        return $this->hasMany(FinancialInvoice::class, 'scholarship_coverage_id');
    }

    // ===== SCOPES =====

    /**
     * Scope to get active scholarships only
     */
    public function scopeActive($query)
    {
        // Use ->value for database comparison (works with both MySQL and SQLite)
        return $query->where('status', ScholarshipStatus::ACTIVE->value);
    }

    /**
     * Scope to check if scholarship is valid for a given date
     * Uses "First Day of Month" rule: valid if valid_from <= date AND (valid_until IS NULL OR valid_until >= date)
     */
    public function scopeValidForDate($query, Carbon $date)
    {
        // Convert Carbon to date string for SQLite compatibility
        $dateString = $date->toDateString();

        return $query
            ->where('valid_from', '<=', $dateString)
            ->where(function ($q) use ($dateString) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', $dateString);
            });
    }

    /**
     * Scope to get scholarships that are active AND valid for a given date
     */
    public function scopeActiveAndValidForDate($query, Carbon $date)
    {
        return $query->active()->validForDate($date);
    }

    /**
     * Scope for a specific student
     */
    public function scopeForStudent($query, string $idRegistrasiMahasiswa)
    {
        return $query->where('id_registrasi_mahasiswa', $idRegistrasiMahasiswa);
    }

    // ===== HELPER METHODS =====

    /**
     * Check if this scholarship assignment is valid for a given date
     */
    public function isValidForDate(Carbon $date): bool
    {
        if ($this->status !== ScholarshipStatus::ACTIVE) {
            return false;
        }

        $validFrom = $this->valid_from instanceof Carbon ? $this->valid_from : Carbon::parse($this->valid_from);
        $validUntil = $this->valid_until instanceof Carbon ? $this->valid_until : ($this->valid_until ? Carbon::parse($this->valid_until) : null);

        $isAfterStart = $validFrom->lte($date);
        $isBeforeEnd = $validUntil === null || $validUntil->gte($date);

        return $isAfterStart && $isBeforeEnd;
    }

    /**
     * Get the coverage percentage from master scholarship
     */
    public function getCoveragePercentage(): float
    {
        return (float) ($this->scholarship?->coverage_percentage ?? 0);
    }

    /**
     * Check if this is a full coverage scholarship
     */
    public function isFullCoverage(): bool
    {
        return $this->getCoveragePercentage() >= 100;
    }

    /**
     * Get coverage type label (Indonesian)
     */
    public function getCoverageTypeLabelAttribute(): string
    {
        return match ($this->coverage_type) {
            'FULL_TUITION' => 'SPP Penuh',
            'PARTIAL_TUITION' => 'SPP Sebagian',
            'TUITION_AND_LIVING' => 'SPP + Biaya Hidup',
            default => $this->coverage_type,
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Models\Finance;

use App\Enums\Finance\InvoiceStatus;
use App\Enums\Finance\PaymentSource;
use App\Models\RiwayatPendidikan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialInvoice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'invoice_number',
        'id_registrasi_mahasiswa',
        'period_date',
        'due_date',
        'total_amount',
        'status',
        'payment_source',
        'scholarship_coverage_id',
        'paid_at',
        'generated_at',
    ];

    protected $casts = [
        'period_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'generated_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'status' => InvoiceStatus::class,
        'payment_source' => PaymentSource::class,
    ];

    // ===== RELATIONSHIPS =====

    public function items(): HasMany
    {
        return $this->hasMany(FinancialInvoiceItem::class);
    }

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(FinancialPayment::class, 'financial_payment_invoice', 'invoice_id', 'payment_id')
            ->withPivot('amount_allocated');
    }

    public function riwayatPendidikan(): BelongsTo
    {
        return $this->belongsTo(RiwayatPendidikan::class, 'id_registrasi_mahasiswa', 'id_registrasi_mahasiswa');
    }

    /**
     * Get the scholarship coverage that paid for this invoice (if any)
     */
    public function scholarshipCoverage(): BelongsTo
    {
        return $this->belongsTo(StudentScholarship::class, 'scholarship_coverage_id');
    }

    // ===== SCOPES =====

    public function scopeUnpaid($query)
    {
        return $query->where('status', InvoiceStatus::UNPAID);
    }

    public function scopePaid($query)
    {
        return $query->where('status', InvoiceStatus::PAID);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', InvoiceStatus::UNPAID)
            ->where('due_date', '<', now());
    }

    public function scopeForPeriod($query, int $year, int $month)
    {
        return $query->whereYear('period_date', $year)
            ->whereMonth('period_date', $month);
    }

    public function scopeScholarshipCovered($query)
    {
        return $query->where('payment_source', PaymentSource::SCHOLARSHIP);
    }

    public function scopeSelfPaid($query)
    {
        return $query->where('payment_source', PaymentSource::SELF_PAYMENT);
    }

    // ===== HELPER METHODS =====

    /**
     * Check if invoice is covered by scholarship
     */
    public function isCoveredByScholarship(): bool
    {
        return $this->payment_source === PaymentSource::SCHOLARSHIP;
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === InvoiceStatus::UNPAID
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(PaymentSource $source, ?int $scholarshipCoverageId = null): bool
    {
        return $this->update([
            'status' => InvoiceStatus::PAID,
            'payment_source' => $source,
            'scholarship_coverage_id' => $scholarshipCoverageId,
            'paid_at' => now(),
        ]);
    }
}

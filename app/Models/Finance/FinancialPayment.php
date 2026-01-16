<?php

declare(strict_types=1);

namespace App\Models\Finance;

use App\Enums\Finance\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;

class FinancialPayment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'payment_number',
        'payment_method',
        'proof_file_path',
        'proof_file_hash',
        'status',
        'verified_at',
        'verified_by',
        'notes',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'payment_method' => PaymentMethod::class,
    ];

    // ===== RELATIONSHIPS =====

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(FinancialInvoice::class, 'financial_payment_invoice', 'payment_id', 'invoice_id')
            ->withPivot('amount_allocated')
            ->with('riwayatPendidikan');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by', 'id');
    }

    // ===== SCOPES =====

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'VERIFIED');
    }

    public function scopeScholarshipPayments($query)
    {
        return $query->where('payment_method', PaymentMethod::SCHOLARSHIP);
    }

    // ===== HELPER METHODS =====

    /**
     * Check if this is a scholarship-based payment
     */
    public function isScholarshipPayment(): bool
    {
        return $this->payment_method === PaymentMethod::SCHOLARSHIP;
    }

    /**
     * Check if this payment requires manual verification
     */
    public function requiresVerification(): bool
    {
        return $this->payment_method->requiresVerification();
    }

    /**
     * Get total amount allocated across all invoices
     */
    public function getTotalAllocatedAttribute(): float
    {
        return (float) $this->invoices->sum('pivot.amount_allocated');
    }
}

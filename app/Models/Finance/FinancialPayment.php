<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class FinancialPayment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'payment_number',
        'payment_method',
        'proof_file_path',
        'proof_file_hash',
        'status', // PENDING, VERIFIED, REJECTED
        'verified_at',
        'verified_by',
        'notes',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function invoices()
    {
        return $this->belongsToMany(FinancialInvoice::class, 'financial_payment_invoice', 'payment_id', 'invoice_id')
            ->withPivot('amount_allocated');
    }

    public function verifier()
    {
        return $this->belongsTo(\App\Models\User::class, 'verified_by');
    }
}

<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class FinancialInvoice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'invoice_number',
        'id_registrasi_mahasiswa',
        'period_date',
        'due_date',
        'total_amount',
        'status', // UNPAID, PAID
        'paid_at',
        'generated_at',
    ];

    protected $casts = [
        'period_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'generated_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(FinancialInvoiceItem::class);
    }

    public function payments()
    {
        return $this->belongsToMany(FinancialPayment::class, 'financial_payment_invoice', 'invoice_id', 'payment_id')
            ->withPivot('amount_allocated');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'UNPAID');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'UNPAID')
            ->where('due_date', '<', now());
    }
}

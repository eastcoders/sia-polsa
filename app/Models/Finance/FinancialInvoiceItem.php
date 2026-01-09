<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'financial_invoice_id',
        'component_name',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(FinancialInvoice::class, 'financial_invoice_id');
    }
}

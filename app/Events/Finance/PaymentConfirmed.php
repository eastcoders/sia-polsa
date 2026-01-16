<?php

declare(strict_types=1);

namespace App\Events\Finance;

use App\Models\Finance\FinancialPayment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when a payment is confirmed/verified.
 * This triggers AKM initialization for the semester based on Hybrid Approach.
 */
class PaymentConfirmed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public FinancialPayment $payment,
        public string $idRegistrasiMahasiswa,
        public string $idSemester
    ) {
    }
}

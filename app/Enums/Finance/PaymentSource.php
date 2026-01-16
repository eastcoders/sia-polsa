<?php

declare(strict_types=1);

namespace App\Enums\Finance;

/**
 * Enum for Payment Source in Financial Invoices
 * Indicates HOW the invoice was paid/covered
 */
enum PaymentSource: string
{
    case SELF_PAYMENT = 'SELF_PAYMENT';
    case SCHOLARSHIP = 'SCHOLARSHIP';
    case DISPENSATION = 'DISPENSATION';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::SELF_PAYMENT => 'Bayar Mandiri',
            self::SCHOLARSHIP => 'Beasiswa',
            self::DISPENSATION => 'Dispensasi',
        };
    }

    /**
     * Get badge color for UI display
     */
    public function color(): string
    {
        return match ($this) {
            self::SELF_PAYMENT => 'primary',
            self::SCHOLARSHIP => 'success',
            self::DISPENSATION => 'warning',
        };
    }
}

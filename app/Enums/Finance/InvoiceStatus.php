<?php

declare(strict_types=1);

namespace App\Enums\Finance;

/**
 * Enum for Invoice Status
 */
enum InvoiceStatus: string
{
    case UNPAID = 'UNPAID';
    case PAID = 'PAID';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::UNPAID => 'Belum Lunas',
            self::PAID => 'Lunas',
        };
    }

    /**
     * Get badge color for UI display
     */
    public function color(): string
    {
        return match ($this) {
            self::UNPAID => 'danger',
            self::PAID => 'success',
        };
    }
}

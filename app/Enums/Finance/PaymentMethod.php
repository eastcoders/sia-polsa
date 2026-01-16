<?php

declare(strict_types=1);

namespace App\Enums\Finance;

/**
 * Enum for Payment Methods in Financial Payments
 */
enum PaymentMethod: string
{
    case MANUAL_TRANSFER = 'MANUAL_TRANSFER';
    case VIRTUAL_ACCOUNT = 'VIRTUAL_ACCOUNT';
    case CASH = 'CASH';
    case SCHOLARSHIP = 'SCHOLARSHIP';
    case WAIVER = 'WAIVER';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::MANUAL_TRANSFER => 'Transfer Manual',
            self::VIRTUAL_ACCOUNT => 'Virtual Account',
            self::CASH => 'Tunai',
            self::SCHOLARSHIP => 'Dana Beasiswa',
            self::WAIVER => 'Pembebasan',
        };
    }

    /**
     * Check if this payment method requires manual verification
     */
    public function requiresVerification(): bool
    {
        return match ($this) {
            self::MANUAL_TRANSFER, self::CASH => true,
            self::VIRTUAL_ACCOUNT, self::SCHOLARSHIP, self::WAIVER => false,
        };
    }

    /**
     * Check if this is an auto-verified payment method
     */
    public function isAutoVerified(): bool
    {
        return match ($this) {
            self::SCHOLARSHIP, self::WAIVER, self::VIRTUAL_ACCOUNT => true,
            self::MANUAL_TRANSFER, self::CASH => false,
        };
    }
}

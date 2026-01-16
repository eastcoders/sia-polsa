<?php

declare(strict_types=1);

namespace App\Enums\Finance;

/**
 * Enum for Student Scholarship Status
 */
enum ScholarshipStatus: string
{
    case ACTIVE = 'ACTIVE';
    case SUSPENDED = 'SUSPENDED';
    case REVOKED = 'REVOKED';
    case EXPIRED = 'EXPIRED';
    case COMPLETED = 'COMPLETED';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::SUSPENDED => 'Ditangguhkan',
            self::REVOKED => 'Dicabut',
            self::EXPIRED => 'Kadaluarsa',
            self::COMPLETED => 'Selesai',
        };
    }

    /**
     * Get badge color for UI display
     */
    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::SUSPENDED => 'warning',
            self::REVOKED => 'danger',
            self::EXPIRED => 'gray',
            self::COMPLETED => 'info',
        };
    }

    /**
     * Check if this status allows invoice coverage
     */
    public function coversInvoice(): bool
    {
        return $this === self::ACTIVE;
    }
}

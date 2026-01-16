<?php

namespace App\Services;

use App\Models\KalenderAkademik;
use Illuminate\Support\Carbon;

/**
 * Service class for determining active exam periods.
 * 
 * Used by Auto-Switch Logic to show only relevant exam schedules
 * (UTS or UAS) based on current date and academic calendar.
 */
class ExamPeriodService
{
    /**
     * Get the active exam type based on today's date.
     * 
     * @return string|null 'UTS', 'UAS', or null if not in exam period
     */
    public static function getActiveExamType(?Carbon $date = null): ?string
    {
        $date = $date ?? now();

        $activeEvent = KalenderAkademik::query()
            ->whereIn('jenis_kegiatan', ['MINGGU_UTS', 'MINGGU_UAS'])
            ->whereDate('tanggal_mulai', '<=', $date)
            ->whereDate('tanggal_selesai', '>=', $date)
            ->first();

        if (!$activeEvent) {
            return null;
        }

        return match ($activeEvent->jenis_kegiatan) {
            'MINGGU_UTS' => 'UTS',
            'MINGGU_UAS' => 'UAS',
            default => null,
        };
    }

    /**
     * Check if today is within a specific exam period.
     * 
     * @param string $type 'UTS' or 'UAS'
     * @return bool
     */
    public static function isExamPeriod(string $type, ?Carbon $date = null): bool
    {
        $date = $date ?? now();

        $jenisKegiatan = match ($type) {
            'UTS' => 'MINGGU_UTS',
            'UAS' => 'MINGGU_UAS',
            default => null,
        };

        if (!$jenisKegiatan) {
            return false;
        }

        return KalenderAkademik::query()
            ->where('jenis_kegiatan', $jenisKegiatan)
            ->whereDate('tanggal_mulai', '<=', $date)
            ->whereDate('tanggal_selesai', '>=', $date)
            ->exists();
    }

    /**
     * Get the upcoming exam type (next exam period).
     * Used as fallback when not currently in an exam period.
     * 
     * @return string|null 'UTS', 'UAS', or null if no upcoming exam
     */
    public static function getUpcomingExamType(?Carbon $date = null): ?string
    {
        $date = $date ?? now();

        $upcomingEvent = KalenderAkademik::query()
            ->whereIn('jenis_kegiatan', ['MINGGU_UTS', 'MINGGU_UAS'])
            ->whereDate('tanggal_mulai', '>', $date)
            ->orderBy('tanggal_mulai', 'asc')
            ->first();

        if (!$upcomingEvent) {
            return null;
        }

        return match ($upcomingEvent->jenis_kegiatan) {
            'MINGGU_UTS' => 'UTS',
            'MINGGU_UAS' => 'UAS',
            default => null,
        };
    }

    /**
     * Get days until next exam period starts.
     * 
     * @return int|null Number of days, or null if no upcoming exam
     */
    public static function getDaysUntilNextExam(?Carbon $date = null): ?int
    {
        $date = $date ?? now();

        $upcomingEvent = KalenderAkademik::query()
            ->whereIn('jenis_kegiatan', ['MINGGU_UTS', 'MINGGU_UAS'])
            ->whereDate('tanggal_mulai', '>', $date)
            ->orderBy('tanggal_mulai', 'asc')
            ->first();

        if (!$upcomingEvent) {
            return null;
        }

        return $date->diffInDays($upcomingEvent->tanggal_mulai);
    }

    /**
     * Get relevant exam type with fallback logic.
     * 
     * Priority:
     * 1. Active exam period (currently ongoing)
     * 2. Upcoming exam (if within 30 days)
     * 3. null (show all/default behavior)
     * 
     * @return string|null 'UTS', 'UAS', or null
     */
    public static function getRelevantExamType(?Carbon $date = null): ?string
    {
        $date = $date ?? now();

        // First check if we're in an active exam period
        $activeType = self::getActiveExamType($date);
        if ($activeType) {
            return $activeType;
        }

        // Check for upcoming exam within 30 days
        $daysUntil = self::getDaysUntilNextExam($date);
        if ($daysUntil !== null && $daysUntil <= 30) {
            return self::getUpcomingExamType($date);
        }

        return null;
    }

    /**
     * Get human-readable status message for student view.
     * 
     * @return array{type: string|null, message: string, is_active: bool}
     */
    public static function getExamPeriodStatus(?Carbon $date = null): array
    {
        $date = $date ?? now();

        $activeType = self::getActiveExamType($date);
        if ($activeType) {
            return [
                'type' => $activeType,
                'message' => "Saat ini adalah periode {$activeType}.",
                'is_active' => true,
            ];
        }

        $upcomingType = self::getUpcomingExamType($date);
        $daysUntil = self::getDaysUntilNextExam($date);

        if ($upcomingType && $daysUntil !== null) {
            return [
                'type' => $upcomingType,
                'message' => "{$upcomingType} akan dimulai dalam {$daysUntil} hari.",
                'is_active' => false,
            ];
        }

        return [
            'type' => null,
            'message' => 'Tidak dalam periode ujian.',
            'is_active' => false,
        ];
    }
}

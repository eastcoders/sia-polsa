<?php

namespace App\Services;

use App\Models\KalenderAkademik;
use App\Models\KelasKuliah;
use Carbon\Carbon;

class ClassScheduleValidationService
{
    /**
     * Validate a proposed schedule date for a class.
     * Returns an array with 'status' (valid, warning, error) and 'message'.
     */
    public function validateScheduleDate(string $date, $kelasKuliahId): array
    {
        $date = Carbon::parse($date);
        $kelas = KelasKuliah::find($kelasKuliahId);

        if (!$kelas) {
            return ['status' => 'error', 'message' => 'Kelas tidak ditemukan.'];
        }

        // 1. Check against Academic Calendar Holidays
        $holiday = KalenderAkademik::active($date)->holidays()->first();
        if ($holiday) {
            return [
                'status' => 'warning',
                'message' => "Tanggal terpilih ({$date->toDateString()}) adalah hari libur: {$holiday->keterangan}. Pastikan ini adalah Kuliah Pengganti yang valid.",
            ];
        }

        // 2. Check Effective Date Range
        if ($kelas->tanggal_mulai_efektif && $date->lt(Carbon::parse($kelas->tanggal_mulai_efektif))) {
            return [
                'status' => 'warning',
                'message' => 'Tanggal pertemuan sebelum Tanggal Mulai Efektif kelas.',
            ];
        }

        if ($kelas->tanggal_akhir_efektif && $date->gt(Carbon::parse($kelas->tanggal_akhir_efektif))) {
            return [
                'status' => 'warning',
                'message' => 'Tanggal pertemuan melewati Tanggal Akhir Efektif kelas. Pastikan ini adalah materi susulan (Make-up Class).',
            ];
        }

        return ['status' => 'valid', 'message' => 'Jadwal valid.'];
    }

    /**
     * Check if a class is eligible for an exam (UTS/UAS) based on realized meeting count.
     */
    public function canAdministerExam($kelasKuliahId, string $examType): array
    {
        $kelas = KelasKuliah::find($kelasKuliahId);
        if (!$kelas) {
            return ['can_proceed' => false, 'message' => 'Kelas tidak ditemukan.'];
        }

        $realizedCount = $kelas->realized_meetings_count;

        $minMeetings = match (strtoupper($examType)) {
            'UTS' => 7,
            'UAS' => 14,
            default => 0,
        };

        if ($realizedCount < $minMeetings) {
            return [
                'can_proceed' => false,
                'message' => "Syarat pertemuan untuk $examType belum terpenuhi. Realisasi: $realizedCount, Minimal: $minMeetings.",
            ];
        }

        return ['can_proceed' => true, 'message' => 'Memenuhi syarat.'];
    }
}

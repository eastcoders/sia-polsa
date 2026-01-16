<?php

declare(strict_types=1);

namespace App\Actions\Academic;

use App\Models\AktivitasKuliahMahasiswa;
use App\Models\Finance\FinancialInvoice;
use App\Models\NilaiKelasPerkuliahan;
use App\Models\RiwayatPendidikan;
use App\Models\Semester;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Action Class: Calculate Student GPA (IPS/IPK) & SKS
 * 
 * This is the core calculation engine that:
 * 1. Loops through ALL semesters from semester 1 to current
 * 2. Calculates IPS (Semester GPA) and IPK (Cumulative GPA)
 * 3. Updates SKS counts (semester & total)
 * 4. Integrates with Finance module for biaya_kuliah_smt
 * 
 * Key Feature: "Butterfly Effect" handling
 * - When nilai changes in past semester, recalculates ALL subsequent semesters
 * - Ensures IPK consistency across entire student history
 */
class CalculateStudentGpaAction
{
    /**
     * PDDikti default values for null handling
     */
    private const DEFAULT_BIAYA_KULIAH = 0.00;
    private const DEFAULT_STATUS = 'A';

    /**
     * Statistics for reporting
     */
    private array $stats = [
        'semesters_processed' => 0,
        'total_sks' => 0,
        'final_ipk' => 0.00,
        'errors' => [],
    ];

    /**
     * Execute the GPA calculation for a single student.
     * 
     * @param string $idRegistrasiMahasiswa The student's registration ID
     * @param bool $dryRun If true, don't actually update records
     * @return array Summary of the calculation
     */
    public function execute(string $idRegistrasiMahasiswa, bool $dryRun = false): array
    {
        Log::info('[GPA CALC] Starting calculation', [
            'id_registrasi' => $idRegistrasiMahasiswa,
            'dry_run' => $dryRun,
        ]);

        try {
            // Validate student exists
            $riwayat = RiwayatPendidikan::where('id_registrasi_mahasiswa', $idRegistrasiMahasiswa)
                ->firstOrFail();

            // Get all nilai grouped by semester, ordered chronologically
            $nilaiPerSemester = $this->getNilaiGroupedBySemester($idRegistrasiMahasiswa);

            if ($nilaiPerSemester->isEmpty()) {
                Log::info('[GPA CALC] No grades found for student', [
                    'id_registrasi' => $idRegistrasiMahasiswa,
                ]);
                return $this->getEmptyResult($idRegistrasiMahasiswa);
            }

            // Process in transaction
            if (!$dryRun) {
                DB::transaction(function () use ($idRegistrasiMahasiswa, $nilaiPerSemester, $riwayat) {
                    $this->processAllSemesters($idRegistrasiMahasiswa, $nilaiPerSemester, $riwayat);
                });
            } else {
                // Dry run - calculate but don't save
                $this->processAllSemesters($idRegistrasiMahasiswa, $nilaiPerSemester, $riwayat, dryRun: true);
            }

            Log::info('[GPA CALC] Calculation completed', $this->stats);

            return [
                'success' => true,
                'id_registrasi_mahasiswa' => $idRegistrasiMahasiswa,
                'nim' => $riwayat->nim,
                ...$this->stats,
            ];

        } catch (\Throwable $e) {
            Log::error('[GPA CALC] Calculation failed', [
                'id_registrasi' => $idRegistrasiMahasiswa,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'id_registrasi_mahasiswa' => $idRegistrasiMahasiswa,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get all grades grouped by semester, ordered chronologically.
     */
    private function getNilaiGroupedBySemester(string $idRegistrasi)
    {
        return NilaiKelasPerkuliahan::with(['kelasKuliah.matkul', 'kelasKuliah.semester'])
            ->where('id_registrasi_mahasiswa', $idRegistrasi)
            ->get()
            ->groupBy(fn($item) => $item->kelasKuliah->id_semester)
            ->sortKeys(); // Chronological order (semester ID format: YYYYP)
    }

    /**
     * Process all semesters sequentially to maintain cumulative consistency.
     * This is the "Butterfly Effect" handler - changes propagate forward.
     */
    private function processAllSemesters(
        string $idRegistrasi,
        $nilaiPerSemester,
        RiwayatPendidikan $riwayat,
        bool $dryRun = false
    ): void {
        $cumulativeSks = 0;
        $cumulativeMutu = 0.0;

        foreach ($nilaiPerSemester as $idSemester => $grades) {
            // Calculate semester values
            $semesterResult = $this->calculateSemester($grades);

            // Update cumulative values
            $cumulativeSks += $semesterResult['sks'];
            $cumulativeMutu += $semesterResult['mutu'];

            // Calculate IPK (Cumulative GPA)
            $ipk = $cumulativeSks > 0
                ? round($cumulativeMutu / $cumulativeSks, 2)
                : 0.00;

            // Get biaya_kuliah_smt from Finance module
            $biayaKuliah = $this->getBiayaKuliahSemester($idRegistrasi, $idSemester);

            // Prepare AKM data
            $akmData = [
                'id_status_mahasiswa' => $this->determineStatus($semesterResult['sks']),
                'ips' => $semesterResult['ips'],
                'ipk' => $ipk,
                'sks_semester' => $semesterResult['sks'],
                'sks_total' => $cumulativeSks,
                'biaya_kuliah_smt' => $biayaKuliah,
            ];

            // Update or create AKM record
            if (!$dryRun) {
                AktivitasKuliahMahasiswa::updateOrCreate(
                    [
                        'id_registrasi_mahasiswa' => $idRegistrasi,
                        'id_semester' => $idSemester,
                    ],
                    $akmData
                );
            }

            $this->stats['semesters_processed']++;
            $this->stats['total_sks'] = $cumulativeSks;
            $this->stats['final_ipk'] = $ipk;
        }
    }

    /**
     * Calculate IPS and SKS for a single semester.
     */
    private function calculateSemester($grades): array
    {
        $sksSemester = 0;
        $mutuSemester = 0.0;

        foreach ($grades as $nilai) {
            $sks = $nilai->kelasKuliah->matkul->sks_mata_kuliah ?? 0;
            $indeks = $nilai->nilai_indeks ?? 0;

            $sksSemester += $sks;
            $mutuSemester += ($sks * $indeks);
        }

        $ips = $sksSemester > 0
            ? round($mutuSemester / $sksSemester, 2)
            : 0.00;

        return [
            'sks' => $sksSemester,
            'mutu' => $mutuSemester,
            'ips' => $ips,
        ];
    }

    /**
     * Get biaya_kuliah_smt from Finance module.
     * Maps invoice period_date to semester.
     * 
     * Edge Case Handling:
     * - If no invoice found, return default value (0.00)
     * - PDDikti accepts 0 but logs warning for review
     */
    private function getBiayaKuliahSemester(string $idRegistrasi, string $idSemester): float
    {
        // Parse semester to date range
        // Semester format: YYYYP where P = 1 (Ganjil: Aug-Jan) or 2 (Genap: Feb-Jul)
        $year = (int) substr($idSemester, 0, 4);
        $period = (int) substr($idSemester, 4, 1);

        if ($period === 1) {
            // Ganjil: August - January
            $startDate = Carbon::create($year, 8, 1);
            $endDate = Carbon::create($year + 1, 1, 31);
        } else {
            // Genap: February - July
            $startDate = Carbon::create($year, 2, 1);
            $endDate = Carbon::create($year, 7, 31);
        }

        // Sum all invoices in this semester period
        $totalBiaya = FinancialInvoice::where('id_registrasi_mahasiswa', $idRegistrasi)
            ->whereBetween('period_date', [$startDate, $endDate])
            ->sum('total_amount');

        if ($totalBiaya == 0) {
            Log::warning('[GPA CALC] No invoice found for semester, using default', [
                'id_registrasi' => $idRegistrasi,
                'semester' => $idSemester,
                'default_biaya' => self::DEFAULT_BIAYA_KULIAH,
            ]);
        }

        return (float) ($totalBiaya ?: self::DEFAULT_BIAYA_KULIAH);
    }

    /**
     * Determine student status based on SKS.
     * Business Rule: If SKS > 0, status is AKTIF.
     * Note: This is a fallback. Primary status is set at registration time.
     */
    private function determineStatus(int $sksSemester): string
    {
        // Default to AKTIF - status should primarily be set at registration
        // This only changes if explicitly needed
        return self::DEFAULT_STATUS;
    }

    /**
     * Return empty result when no grades found.
     */
    private function getEmptyResult(string $idRegistrasi): array
    {
        return [
            'success' => true,
            'id_registrasi_mahasiswa' => $idRegistrasi,
            'semesters_processed' => 0,
            'total_sks' => 0,
            'final_ipk' => 0.00,
            'message' => 'No grades found for this student',
        ];
    }

    /**
     * Execute calculation for multiple students (batch mode).
     * Used by nightly cron job.
     * 
     * @param array $studentIds Array of id_registrasi_mahasiswa
     * @return array Summary of batch operation
     */
    public function executeBatch(array $studentIds): array
    {
        $results = [
            'total' => count($studentIds),
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($studentIds as $studentId) {
            $result = $this->execute($studentId);

            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'id_registrasi' => $studentId,
                    'error' => $result['error'] ?? 'Unknown error',
                ];
            }

            // Reset stats for next student
            $this->stats = [
                'semesters_processed' => 0,
                'total_sks' => 0,
                'final_ipk' => 0.00,
                'errors' => [],
            ];
        }

        return $results;
    }
}

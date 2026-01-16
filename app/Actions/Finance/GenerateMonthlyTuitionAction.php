<?php

declare(strict_types=1);

namespace App\Actions\Finance;

use App\Enums\Finance\InvoiceStatus;
use App\Enums\Finance\PaymentMethod;
use App\Enums\Finance\PaymentSource;
use App\Enums\Finance\ScholarshipStatus;
use App\Models\Finance\FinancialInvoice;
use App\Models\Finance\FinancialPayment;
use App\Models\Finance\StudentScholarship;
use App\Models\RiwayatPendidikan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Action Class: Generate Monthly Tuition Invoices
 * 
 * This is the core "Billing Engine" that runs on the 1st of every month.
 * It implements "Shadow Billing" logic:
 * - Generates invoices for ALL active students
 * - Students with active scholarships get auto-paid invoices
 * - Students without scholarships get UNPAID invoices
 * 
 * @see https://refactoring.guru/design-patterns/command
 */
class GenerateMonthlyTuitionAction
{
    /**
     * Configuration
     */
    private const DUE_DATE_OFFSET_DAYS = 15; // Due date is 15 days after period start
    private const SYSTEM_USER_EMAIL = 'system@siappp.local'; // System user for auto-verification

    /**
     * Statistics for reporting
     */
    private int $totalStudentsProcessed = 0;
    private int $invoicesCreated = 0;
    private int $scholarshipInvoices = 0;
    private int $regularInvoices = 0;
    private int $duplicatesSkipped = 0;
    private array $errors = [];

    /**
     * Execute the billing generation for a specific month
     * 
     * @param Carbon|null $periodDate The first day of the billing month (defaults to current month)
     * @param bool $dryRun If true, don't actually create records (for testing)
     * @return array Summary of the operation
     */
    public function execute(?Carbon $periodDate = null, bool $dryRun = false): array
    {
        $periodDate = $periodDate ?? Carbon::now()->startOfMonth();
        $periodDate = $periodDate->startOfMonth(); // Ensure it's always day 1

        Log::info('[BILLING ENGINE] Starting monthly tuition generation', [
            'period' => $periodDate->format('Y-m'),
            'dry_run' => $dryRun,
        ]);

        // Step 1: Fetch all active students
        $activeStudents = $this->getActiveStudents();

        Log::info('[BILLING ENGINE] Found active students', [
            'count' => $activeStudents->count(),
        ]);

        // Step 2: Process each student
        foreach ($activeStudents as $student) {
            try {
                $this->processStudent($student, $periodDate, $dryRun);
                $this->totalStudentsProcessed++;
            } catch (\Throwable $e) {
                $this->errors[] = [
                    'student_id' => $student->id_registrasi_mahasiswa,
                    'error' => $e->getMessage(),
                ];
                Log::error('[BILLING ENGINE] Error processing student', [
                    'student_id' => $student->id_registrasi_mahasiswa,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $summary = $this->getSummary($periodDate);

        Log::info('[BILLING ENGINE] Completed monthly tuition generation', $summary);

        return $summary;
    }

    /**
     * Get all active students from riwayat_pendidikans
     * Status is stored in aktivitas_kuliah_mahasiswas table per semester
     * 'A' = Aktif, 'C' = Cuti, 'N' = Non-Aktif, 'L' = Lulus, 'D' = DO
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getActiveStudents()
    {
        return RiwayatPendidikan::query()
            ->whereNotNull('id_registrasi_mahasiswa')
            ->whereHas('aktivitasKuliahMahasiswa', function ($query) {
                $query->where('id_status_mahasiswa', 'A'); // Aktif
            })
            ->get();
    }

    /**
     * Process a single student - check scholarship and generate invoice
     * 
     * @param RiwayatPendidikan $student
     * @param Carbon $periodDate
     * @param bool $dryRun
     */
    private function processStudent(RiwayatPendidikan $student, Carbon $periodDate, bool $dryRun): void
    {
        $studentId = $student->id_registrasi_mahasiswa;

        // Step 2A: Check for duplicate (prevent double billing)
        if ($this->invoiceExistsForPeriod($studentId, $periodDate)) {
            $this->duplicatesSkipped++;
            Log::debug('[BILLING ENGINE] Skipping duplicate invoice', [
                'student_id' => $studentId,
                'period' => $periodDate->format('Y-m'),
            ]);
            return;
        }

        // Step 2B: Check for active scholarship
        $activeScholarship = $this->getActiveScholarshipForDate($studentId, $periodDate);

        // Step 3: Calculate tuition amount
        $tuitionAmount = $this->calculateTuitionAmount($student);

        if ($dryRun) {
            // Just count, don't create
            $this->invoicesCreated++;
            if ($activeScholarship) {
                $this->scholarshipInvoices++;
            } else {
                $this->regularInvoices++;
            }
            return;
        }

        // Step 4: Generate invoice and handle scholarship payment
        DB::transaction(function () use ($studentId, $periodDate, $tuitionAmount, $activeScholarship) {
            // Create the invoice (always UNPAID initially)
            $invoice = $this->createInvoice($studentId, $periodDate, $tuitionAmount);
            $this->invoicesCreated++;

            // Step 5: If scholarship is active, create auto-payment
            if ($activeScholarship !== null) {
                $this->createScholarshipPayment($invoice, $activeScholarship);
                $this->scholarshipInvoices++;
            } else {
                $this->regularInvoices++;
            }
        });
    }

    /**
     * Check if an invoice already exists for this student and period
     */
    private function invoiceExistsForPeriod(string $studentId, Carbon $periodDate): bool
    {
        return FinancialInvoice::query()
            ->where('id_registrasi_mahasiswa', $studentId)
            ->whereYear('period_date', $periodDate->year)
            ->whereMonth('period_date', $periodDate->month)
            ->exists();
    }

    /**
     * Get active scholarship for a student on a specific date
     * Uses "First Day of Month" rule for coverage check
     * 
     * @param string $studentId
     * @param Carbon $date The period date (first day of month)
     * @return StudentScholarship|null
     */
    private function getActiveScholarshipForDate(string $studentId, Carbon $date): ?StudentScholarship
    {
        // Debug: Log the query parameters
        Log::debug('[BILLING ENGINE] Checking scholarship', [
            'student_id' => $studentId,
            'date' => $date->toDateString(),
            'total_scholarships' => StudentScholarship::count(),
            'for_student' => StudentScholarship::forStudent($studentId)->count(),
        ]);

        $scholarship = StudentScholarship::query()
            ->forStudent($studentId)
            ->activeAndValidForDate($date)
            ->with('scholarship') // Eager load master data
            ->first();

        Log::debug('[BILLING ENGINE] Scholarship result', [
            'found' => $scholarship !== null,
            'id' => $scholarship?->id,
        ]);

        return $scholarship;
    }

    /**
     * Calculate tuition amount for a student
     * 
     * @param RiwayatPendidikan $student
     * @return float
     */
    private function calculateTuitionAmount(RiwayatPendidikan $student): float
    {
        // TODO: Implement proper fee calculation based on:
        // - student_fee_assignments
        // - fee_structures
        // - angkatan, prodi, waktu_kuliah

        // For now, return a placeholder amount
        // This should be replaced with actual fee lookup logic
        return 3000000.00; // Rp 3.000.000 default
    }

    /**
     * Create a new invoice for a student
     * 
     * @param string $studentId
     * @param Carbon $periodDate
     * @param float $amount
     * @return FinancialInvoice
     */
    private function createInvoice(string $studentId, Carbon $periodDate, float $amount): FinancialInvoice
    {
        $invoiceNumber = $this->generateInvoiceNumber($periodDate);
        $dueDate = $periodDate->copy()->addDays(self::DUE_DATE_OFFSET_DAYS);

        return FinancialInvoice::create([
            'invoice_number' => $invoiceNumber,
            'id_registrasi_mahasiswa' => $studentId,
            'period_date' => $periodDate,
            'due_date' => $dueDate,
            'total_amount' => $amount,
            'status' => InvoiceStatus::UNPAID, // Always start as UNPAID
            'payment_source' => null, // Will be set when paid
            'scholarship_coverage_id' => null, // Will be set if scholarship covers
            'generated_at' => now(),
        ]);
    }

    /**
     * Create an automatic scholarship payment for an invoice
     * This is the "Shadow Billing" magic - invoice exists but is immediately paid by scholarship
     * 
     * @param FinancialInvoice $invoice
     * @param StudentScholarship $scholarship
     */
    private function createScholarshipPayment(FinancialInvoice $invoice, StudentScholarship $scholarship): void
    {
        $amount = (float) $invoice->total_amount;
        $coveragePercentage = $scholarship->getCoveragePercentage();

        // Calculate covered amount based on scholarship percentage
        $coveredAmount = $amount * ($coveragePercentage / 100);

        // Get or create system user for auto-verification
        $systemUser = $this->getSystemUser();

        // Create payment record
        $payment = FinancialPayment::create([
            'payment_number' => $this->generatePaymentNumber(Carbon::parse($invoice->period_date)),
            'payment_method' => PaymentMethod::SCHOLARSHIP,
            'proof_file_path' => null, // No proof needed for scholarship
            'proof_file_hash' => null,
            'status' => 'VERIFIED', // Auto-verified
            'verified_at' => now(),
            'verified_by' => $systemUser?->id,
            'notes' => sprintf(
                'Auto-generated: %s (%s) - Coverage: %s%%',
                $scholarship->scholarship->name ?? 'Unknown Scholarship',
                $scholarship->scholarship->code ?? 'N/A',
                number_format($coveragePercentage, 0)
            ),
        ]);

        // Link payment to invoice via pivot table
        $payment->invoices()->attach($invoice->id, [
            'amount_allocated' => $coveredAmount,
        ]);

        // Update invoice status to PAID and link scholarship
        $invoice->update([
            'status' => InvoiceStatus::PAID,
            'payment_source' => PaymentSource::SCHOLARSHIP,
            'scholarship_coverage_id' => $scholarship->id,
            'paid_at' => now(),
        ]);

        Log::info('[BILLING ENGINE] Created scholarship payment', [
            'invoice_id' => $invoice->id,
            'scholarship_id' => $scholarship->id,
            'amount_covered' => $coveredAmount,
        ]);
    }

    /**
     * Generate unique invoice number
     * Format: INV/YYYY/MM/XXXXX
     */
    private function generateInvoiceNumber(Carbon $date): string
    {
        $prefix = sprintf('INV/%s/%s/', $date->format('Y'), $date->format('m'));

        // Get count of invoices for this month to generate sequence
        $count = FinancialInvoice::query()
            ->where('invoice_number', 'like', $prefix . '%')
            ->count();

        return $prefix . str_pad((string) ($count + 1), 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique payment number for scholarship payments
     * Format: PAY/SCH/YYYY/MM/XXXXX
     */
    private function generatePaymentNumber(Carbon $date): string
    {
        $prefix = sprintf('PAY/SCH/%s/%s/', $date->format('Y'), $date->format('m'));

        $count = FinancialPayment::query()
            ->where('payment_number', 'like', $prefix . '%')
            ->count();

        return $prefix . str_pad((string) ($count + 1), 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get or create system user for auto-verification
     */
    private function getSystemUser(): ?User
    {
        return User::firstOrCreate(
            ['email' => self::SYSTEM_USER_EMAIL],
            [
                'name' => 'System',
                'password' => bcrypt(Str::random(32)), // Random password (never used)
            ]
        );
    }

    /**
     * Get summary of the billing operation
     */
    private function getSummary(Carbon $periodDate): array
    {
        return [
            'period' => $periodDate->format('Y-m'),
            'total_students_processed' => $this->totalStudentsProcessed,
            'invoices_created' => $this->invoicesCreated,
            'scholarship_invoices' => $this->scholarshipInvoices,
            'regular_invoices' => $this->regularInvoices,
            'duplicates_skipped' => $this->duplicatesSkipped,
            'errors_count' => count($this->errors),
            'errors' => $this->errors,
        ];
    }

    /**
     * Reset statistics (useful for testing)
     */
    public function resetStats(): void
    {
        $this->totalStudentsProcessed = 0;
        $this->invoicesCreated = 0;
        $this->scholarshipInvoices = 0;
        $this->regularInvoices = 0;
        $this->duplicatesSkipped = 0;
        $this->errors = [];
    }
}

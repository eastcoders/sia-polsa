<?php

namespace Tests\Feature\Finance;

use App\Actions\Finance\GenerateMonthlyTuitionAction;
use App\Enums\Finance\InvoiceStatus;
use App\Enums\Finance\PaymentMethod;
use App\Enums\Finance\PaymentSource;
use App\Enums\Finance\ScholarshipStatus;
use App\Models\AktivitasKuliahMahasiswa;
use App\Models\BiodataMahasiswa;
use App\Models\Finance\FinancialInvoice;
use App\Models\Finance\FinancialPayment;
use App\Models\Finance\Scholarship;
use App\Models\Finance\StudentScholarship;
use App\Models\RiwayatPendidikan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MonthlyBillingTest extends TestCase
{
    use RefreshDatabase;

    protected GenerateMonthlyTuitionAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new GenerateMonthlyTuitionAction();
    }

    /**
     * Helper: Create student with active status
     */
    protected function createActiveStudent(string $id = 'student-001'): RiwayatPendidikan
    {
        // Create AKM record with Aktif status
        AktivitasKuliahMahasiswa::create([
            'id_registrasi_mahasiswa' => $id,
            'id_semester' => '20241',
            'id_status_mahasiswa' => 'A', // Aktif
            'ips' => 3.5,
            'ipk' => 3.5,
            'sks_semester' => 20,
            'sks_total' => 40,
        ]);

        return RiwayatPendidikan::create([
            'id_registrasi_mahasiswa' => $id,
            'id_mahasiswa' => $id,
            'id_biodata_mahasiswa' => $id,
            'nim' => '2024' . rand(10000, 99999),
            'id_jenis_daftar' => '1',
            'id_jalur_daftar' => '1',
            'id_periode_masuk' => '20241',
            'tanggal_daftar' => now(),
            'id_perguruan_tinggi' => 'pt-001',
            'id_prodi' => 'prodi-001',
            'biaya_masuk' => '5000000',
        ]);
    }

    /**
     * Helper: Create scholarship for student
     */
    protected function createScholarship(
        string $studentId,
        int $coveragePercentage = 100,
        ?Carbon $validUntil = null,
        ?Carbon $validFrom = null
    ): StudentScholarship {
        $scholarship = Scholarship::create([
            'name' => 'Test Beasiswa',
            'code' => 'TST-' . rand(100, 999),
            'funding_source' => 'INSTITUTION',
            'coverage_percentage' => $coveragePercentage,
            'is_active' => true,
        ]);

        return StudentScholarship::create([
            'id_registrasi_mahasiswa' => $studentId,
            'scholarship_id' => $scholarship->id,
            'valid_from' => $validFrom ?? Carbon::create(2025, 1, 1), // Default: Jan 1, 2025 (well before any test date)
            'valid_until' => $validUntil,
            'coverage_type' => 'FULL_TUITION',
            'status' => ScholarshipStatus::ACTIVE->value, // Use string value for DB storage
        ]);
    }

    // =========================================================================
    // TEST GROUP 1: Regular Student Billing (No Scholarship)
    // =========================================================================

    /** @test */
    public function it_generates_unpaid_invoice_for_regular_student()
    {
        // Arrange
        $student = $this->createActiveStudent('reg-student-001');
        $periodDate = Carbon::create(2026, 1, 1);

        // Act
        $result = $this->action->execute($periodDate);

        // Assert
        $this->assertEquals(1, $result['invoices_created']);
        $this->assertEquals(1, $result['regular_invoices']);
        $this->assertEquals(0, $result['scholarship_invoices']);

        $invoice = FinancialInvoice::where('id_registrasi_mahasiswa', 'reg-student-001')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(InvoiceStatus::UNPAID, $invoice->status);
        $this->assertNull($invoice->payment_source);
    }

    /** @test */
    public function it_prevents_duplicate_invoice_for_same_period()
    {
        // Arrange
        $student = $this->createActiveStudent('dup-student-001');
        $periodDate = Carbon::create(2026, 1, 1);

        // Act - Run twice
        $result1 = $this->action->execute($periodDate);
        $this->action->resetStats(); // Reset for second run
        $result2 = $this->action->execute($periodDate);

        // Assert
        $this->assertEquals(1, $result1['invoices_created']);
        $this->assertEquals(0, $result2['invoices_created']);
        $this->assertEquals(1, $result2['duplicates_skipped']);

        // Only 1 invoice should exist
        $invoiceCount = FinancialInvoice::where('id_registrasi_mahasiswa', 'dup-student-001')->count();
        $this->assertEquals(1, $invoiceCount);
    }

    // =========================================================================
    // TEST GROUP 2: Scholarship Student Billing (Shadow Billing)
    // =========================================================================

    /** @test */
    public function it_generates_paid_invoice_with_auto_payment_for_scholarship_student()
    {
        // Arrange
        $student = $this->createActiveStudent('sch-student-001');
        $this->createScholarship('sch-student-001', 100); // Full coverage
        $periodDate = Carbon::create(2026, 1, 1);

        // Ensure system user exists
        User::create([
            'name' => 'System',
            'email' => 'system@siappp.local',
            'password' => bcrypt('password'),
        ]);

        // Debug: Check scholarship was created
        $studentScholarship = StudentScholarship::where('id_registrasi_mahasiswa', 'sch-student-001')->first();
        $this->assertNotNull($studentScholarship, 'StudentScholarship record was not created');
        $this->assertEquals(ScholarshipStatus::ACTIVE, $studentScholarship->status);

        // Debug: Check query scope works
        $activeScholarship = StudentScholarship::forStudent('sch-student-001')
            ->activeAndValidForDate($periodDate)
            ->first();
        $this->assertNotNull($activeScholarship, 'Active scholarship query returned null - scope issue');

        // Act
        $result = $this->action->execute($periodDate);

        // Debug: Dump result for analysis
        // dump($result);

        // Assert invoices_created first to verify student was found
        $this->assertEquals(1, $result['invoices_created'], 'No invoice created - student may not be found by getActiveStudents()');
        $this->assertEquals(1, $result['scholarship_invoices'], 'Invoice created but not marked as scholarship - getActiveScholarshipForDate failed');
        $this->assertEquals(0, $result['regular_invoices']);

        // Invoice should be PAID
        $invoice = FinancialInvoice::where('id_registrasi_mahasiswa', 'sch-student-001')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(InvoiceStatus::PAID, $invoice->status);
        $this->assertEquals(PaymentSource::SCHOLARSHIP, $invoice->payment_source);
        $this->assertNotNull($invoice->scholarship_coverage_id);

        // Scholarship payment should exist
        $payment = FinancialPayment::where('payment_method', PaymentMethod::SCHOLARSHIP)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('VERIFIED', $payment->status);
    }

    // =========================================================================
    // TEST GROUP 3: Scholarship Boundary Testing (Date Edges)
    // =========================================================================

    /** @test */
    public function it_covers_invoice_when_scholarship_valid_on_first_day_of_month()
    {
        // Arrange
        $student = $this->createActiveStudent('edge-student-001');

        // Scholarship valid until Jan 1 (exactly the billing date)
        $this->createScholarship('edge-student-001', 100, Carbon::create(2026, 1, 1));

        $periodDate = Carbon::create(2026, 1, 1);

        User::create([
            'name' => 'System',
            'email' => 'system@siappp.local',
            'password' => bcrypt('password'),
        ]);

        // Act
        $result = $this->action->execute($periodDate);

        // Assert - Should be covered (valid_until >= period_date)
        $this->assertEquals(1, $result['scholarship_invoices']);

        $invoice = FinancialInvoice::where('id_registrasi_mahasiswa', 'edge-student-001')->first();
        $this->assertEquals(InvoiceStatus::PAID, $invoice->status);
    }

    /** @test */
    public function it_does_not_cover_invoice_when_scholarship_expired_before_billing()
    {
        // Arrange
        $student = $this->createActiveStudent('exp-student-001');

        // Scholarship expired on Dec 31 (day before billing)
        $this->createScholarship('exp-student-001', 100, Carbon::create(2025, 12, 31));

        $periodDate = Carbon::create(2026, 1, 1);

        // Act
        $result = $this->action->execute($periodDate);

        // Assert - Should NOT be covered (expired)
        $this->assertEquals(0, $result['scholarship_invoices']);
        $this->assertEquals(1, $result['regular_invoices']);

        $invoice = FinancialInvoice::where('id_registrasi_mahasiswa', 'exp-student-001')->first();
        $this->assertEquals(InvoiceStatus::UNPAID, $invoice->status);
    }

    /** @test */
    public function it_covers_invoice_when_scholarship_has_no_expiry_date()
    {
        // Arrange
        $student = $this->createActiveStudent('unlimited-student-001');

        // Scholarship with no expiry (valid_until = null)
        $this->createScholarship('unlimited-student-001', 100, null);

        $periodDate = Carbon::create(2026, 1, 1);

        User::create([
            'name' => 'System',
            'email' => 'system@siappp.local',
            'password' => bcrypt('password'),
        ]);

        // Act
        $result = $this->action->execute($periodDate);

        // Assert - Should be covered (unlimited)
        $this->assertEquals(1, $result['scholarship_invoices']);

        $invoice = FinancialInvoice::where('id_registrasi_mahasiswa', 'unlimited-student-001')->first();
        $this->assertEquals(InvoiceStatus::PAID, $invoice->status);
    }

    // =========================================================================
    // TEST GROUP 4: Dry Run Mode
    // =========================================================================

    /** @test */
    public function dry_run_does_not_create_actual_records()
    {
        // Arrange
        $student = $this->createActiveStudent('dryrun-student-001');
        $periodDate = Carbon::create(2026, 1, 1);

        // Act
        $result = $this->action->execute($periodDate, dryRun: true);

        // Assert
        $this->assertEquals(1, $result['invoices_created']); // Counted but...

        // No actual invoice created
        $invoiceCount = FinancialInvoice::where('id_registrasi_mahasiswa', 'dryrun-student-001')->count();
        $this->assertEquals(0, $invoiceCount);
    }
}

<?php

namespace Tests\Feature\Finance;

use Tests\TestCase;
use App\Models\User;
use App\Models\RiwayatPendidikan;
use App\Models\Finance\FinancialInvoice;
use App\Models\Finance\ExamDispensation;
use App\Services\FinanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GatekeeperTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FinanceService();
    }

    /** @test */
    public function it_allows_access_if_student_has_no_overdue_invoices()
    {
        // 1. Create Student
        // Mocking RiwayatPendidikan minimal structure just for ID reference
        // Assuming 'id' is UUID or Int, Gatekeeper uses the string ID
        $studentId = 'student-uuid-1';

        // 2. Create Invoice (Clean / Not Overdue)
        FinancialInvoice::create([
            'invoice_number' => 'INV-001',
            'id_registrasi_mahasiswa' => $studentId,
            'period_date' => now(),
            'due_date' => now()->addDays(7), // Future
            'total_amount' => 100000,
            'status' => 'UNPAID',
        ]);

        // 3. Check Access
        $result = $this->service->canAccessExam($studentId, 'UAS');

        $this->assertTrue($result['allowed']);
    }

    /** @test */
    public function it_blocks_access_if_student_has_overdue_invoices()
    {
        $studentId = 'student-uuid-2';

        // 2. Create Overdue Invoice
        FinancialInvoice::create([
            'invoice_number' => 'INV-002',
            'id_registrasi_mahasiswa' => $studentId,
            'period_date' => now()->subMonth(),
            'due_date' => now()->subDay(), // Yesterday!
            'total_amount' => 100000,
            'status' => 'UNPAID',
        ]);

        // 3. Check Access
        $result = $this->service->canAccessExam($studentId, 'UAS');

        $this->assertFalse($result['allowed']);
        $this->assertEquals('Outstanding Overdue Invoices', $result['reason']);
    }

    /** @test */
    public function it_allows_access_if_dispensation_exists_for_overdue_student()
    {
        $studentId = 'student-uuid-3';
        $admin = User::factory()->create();

        // 2. Create Overdue Invoice
        FinancialInvoice::create([
            'invoice_number' => 'INV-003',
            'id_registrasi_mahasiswa' => $studentId,
            'period_date' => now()->subMonth(),
            'due_date' => now()->subDay(), // Overdue
            'total_amount' => 100000,
            'status' => 'UNPAID',
        ]);

        // 3. Create Dispensation
        ExamDispensation::create([
            'id_registrasi_mahasiswa' => $studentId,
            'type' => 'UAS',
            'valid_until' => now()->addDay(), // Valid until tomorrow
            'reason' => 'Waiting for scholarship',
            'approved_by' => $admin->id,
        ]);

        // 4. Check Access
        $result = $this->service->canAccessExam($studentId, 'UAS');

        $this->assertTrue($result['allowed']);
        $this->assertEquals('Dispensation Active', $result['reason']);
    }

    /** @test */
    public function it_blocks_access_if_dispensation_is_expired()
    {
        $studentId = 'student-uuid-4';
        $admin = User::factory()->create();

        // 2. Create Overdue Invoice
        FinancialInvoice::create([
            'invoice_number' => 'INV-004',
            'id_registrasi_mahasiswa' => $studentId,
            'period_date' => now()->subMonth(),
            'due_date' => now()->subDay(),
            'total_amount' => 100000,
            'status' => 'UNPAID',
        ]);

        // 3. Create Expired Dispensation
        ExamDispensation::create([
            'id_registrasi_mahasiswa' => $studentId,
            'type' => 'UAS',
            'valid_until' => now()->subDay(), // Expired yesterday
            'reason' => 'Expired promise',
            'approved_by' => $admin->id,
        ]);

        // 4. Check Access
        $result = $this->service->canAccessExam($studentId, 'UAS');

        $this->assertFalse($result['allowed']);
    }
}

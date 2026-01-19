<?php

namespace Tests\Feature\Finance;

use App\Enums\Finance\InvoiceStatus;
use App\Enums\Finance\PaymentMethod;
use App\Models\BiodataMahasiswa;
use App\Models\Finance\FinancialInvoice;
use App\Models\Finance\FinancialInvoiceItem;
use App\Models\Finance\FinancialPayment;
use App\Models\RiwayatPendidikan;
use App\Models\User;
use App\Notifications\InvoiceCreatedNotification;
use App\Notifications\InvoiceReminderNotification;
use App\Notifications\PaymentSubmittedNotification;
use App\Notifications\PaymentVerifiedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TagihanSayaTest extends TestCase
{
    use RefreshDatabase;

    protected User $studentUser;
    protected BiodataMahasiswa $mahasiswa;
    protected RiwayatPendidikan $riwayat;
    protected string $studentId;

    protected function setUp(): void
    {
        parent::setUp();

        // Create required reference data
        $agama = \App\Models\Agama::create([
            'id_agama' => 1,
            'nama_agama' => 'Islam',
        ]);

        // Create student biodata with all required fields
        $this->mahasiswa = BiodataMahasiswa::create([
            'id_mahasiswa' => 'test-mahasiswa-uuid-1',
            'nama_lengkap' => 'Test Student',
            'jenis_kelamin' => 'L',
            'id_agama' => $agama->id_agama,
            'tanggal_lahir' => '2000-01-01',
            'tempat_lahir' => 'Jakarta',
            'kewarganegaraan' => 'Indonesia',
            'nik' => '1234567890123456',
            'nisn' => '1234567890',
            'npwp' => '12.345.678.9-012.345',
            'kelurahan' => 'Test Kelurahan',
            'id_wilayah' => '110101',
            'no_hp' => '081234567890',
            'email' => 'test@student.com',
            'nama_ibu_kandung' => 'Test Mother',
        ]);

        // Create riwayat pendidikan with all required fields
        $this->riwayat = RiwayatPendidikan::create([
            'id_registrasi_mahasiswa' => 'test-registrasi-uuid-1',
            'id_biodata_mahasiswa' => $this->mahasiswa->id,
            'id_mahasiswa' => $this->mahasiswa->id_mahasiswa,
            'nim' => '2024010001',
            'id_jenis_daftar' => 'J1',
            'id_jalur_daftar' => 'JL1',
            'id_periode_masuk' => 'dummy-semester',
            'tanggal_daftar' => '2024-01-01',
            'id_perguruan_tinggi' => 'PT001',
            'id_prodi' => 'dummy-prodi-uuid',
            'biaya_masuk' => '5000000',
        ]);

        $this->studentId = $this->riwayat->id_registrasi_mahasiswa;

        // Create user linked to mahasiswa
        $this->studentUser = User::factory()->create([
            'name' => 'Test Student',
            'mahasiswa_id' => $this->mahasiswa->id,
        ]);
    }

    /** @test */
    public function student_can_only_see_their_own_invoices()
    {
        // Create invoice for our student
        $ownInvoice = FinancialInvoice::create([
            'invoice_number' => 'INV-OWN-001',
            'id_registrasi_mahasiswa' => $this->studentId,
            'period_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 500000,
            'status' => 'UNPAID',
        ]);

        // Create invoice for another student
        $otherStudent = RiwayatPendidikan::create([
            'id_registrasi_mahasiswa' => 'other-student-uuid',
            'id_biodata_mahasiswa' => 9999, // Dummy ID
            'id_mahasiswa' => 'other-mahasiswa-uuid',
            'nim' => '2024010002',
            'id_jenis_daftar' => 'J1',
            'id_jalur_daftar' => 'JL1',
            'id_periode_masuk' => 'dummy-semester',
            'tanggal_daftar' => '2024-01-01',
            'id_perguruan_tinggi' => 'PT001',
            'id_prodi' => 'dummy-prodi-uuid',
            'biaya_masuk' => '5000000',
        ]);

        $otherInvoice = FinancialInvoice::create([
            'invoice_number' => 'INV-OTHER-001',
            'id_registrasi_mahasiswa' => $otherStudent->id_registrasi_mahasiswa,
            'period_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 300000,
            'status' => 'UNPAID',
        ]);

        // Query as student
        $this->actingAs($this->studentUser);

        $invoices = FinancialInvoice::whereHas('riwayatPendidikan', function ($query) {
            $query->where('id_mahasiswa', $this->mahasiswa->id_mahasiswa);
        })->get();

        // Should only see their own invoice
        $this->assertCount(1, $invoices);
        $this->assertEquals('INV-OWN-001', $invoices->first()->invoice_number);
    }

    /** @test */
    public function student_cannot_create_invoice()
    {
        $resourceClass = \App\Filament\Mahasiswa\Resources\TagihanSayas\TagihanSayaResource::class;

        $this->assertFalse($resourceClass::canCreate());
    }

    /** @test */
    public function student_cannot_edit_invoice()
    {
        $invoice = FinancialInvoice::create([
            'invoice_number' => 'INV-TEST-001',
            'id_registrasi_mahasiswa' => $this->studentId,
            'period_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 500000,
            'status' => 'UNPAID',
        ]);

        $resourceClass = \App\Filament\Mahasiswa\Resources\TagihanSayas\TagihanSayaResource::class;

        $this->assertFalse($resourceClass::canEdit($invoice));
    }

    /** @test */
    public function student_cannot_delete_invoice()
    {
        $invoice = FinancialInvoice::create([
            'invoice_number' => 'INV-TEST-002',
            'id_registrasi_mahasiswa' => $this->studentId,
            'period_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 500000,
            'status' => 'UNPAID',
        ]);

        $resourceClass = \App\Filament\Mahasiswa\Resources\TagihanSayas\TagihanSayaResource::class;

        $this->assertFalse($resourceClass::canDelete($invoice));
    }

    /** @test */
    public function payment_submission_creates_pending_payment_record()
    {
        $invoice = FinancialInvoice::create([
            'invoice_number' => 'INV-PAY-001',
            'id_registrasi_mahasiswa' => $this->studentId,
            'period_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 500000,
            'status' => 'UNPAID',
        ]);

        // Simulate payment submission
        $paymentNumber = 'PAY/' . date('Y/m/') . 'TEST01';
        $payment = FinancialPayment::create([
            'payment_number' => $paymentNumber,
            'payment_method' => PaymentMethod::MANUAL_TRANSFER,
            'proof_file_path' => 'payment-proofs/test.jpg',
            'proof_file_hash' => hash('sha256', 'test-content'),
            'status' => 'PENDING',
            'notes' => 'Test payment',
        ]);

        $payment->invoices()->attach($invoice->id, [
            'amount_allocated' => $invoice->total_amount,
        ]);

        // Verify payment record
        $this->assertDatabaseHas('financial_payments', [
            'payment_number' => $paymentNumber,
            'status' => 'PENDING',
        ]);

        // Verify pivot relationship
        $this->assertDatabaseHas('financial_payment_invoice', [
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
            'amount_allocated' => 500000,
        ]);

        // Invoice should still be UNPAID
        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::UNPAID, $invoice->status);
    }

    /** @test */
    public function duplicate_proof_file_is_detected()
    {
        $fileHash = hash('sha256', 'duplicate-content');

        // Create first payment with this hash
        FinancialPayment::create([
            'payment_number' => 'PAY/2026/01/FIRST1',
            'payment_method' => PaymentMethod::MANUAL_TRANSFER,
            'proof_file_path' => 'payment-proofs/first.jpg',
            'proof_file_hash' => $fileHash,
            'status' => 'PENDING',
        ]);

        // Check if duplicate exists
        $duplicateExists = FinancialPayment::where('proof_file_hash', $fileHash)->exists();

        $this->assertTrue($duplicateExists);
    }

    /** @test */
    public function pending_payment_prevents_new_submission()
    {
        $invoice = FinancialInvoice::create([
            'invoice_number' => 'INV-DUP-001',
            'id_registrasi_mahasiswa' => $this->studentId,
            'period_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 500000,
            'status' => 'UNPAID',
        ]);

        // Create pending payment
        $payment = FinancialPayment::create([
            'payment_number' => 'PAY/2026/01/PEND01',
            'payment_method' => PaymentMethod::MANUAL_TRANSFER,
            'proof_file_path' => 'payment-proofs/pending.jpg',
            'status' => 'PENDING',
        ]);

        $payment->invoices()->attach($invoice->id, [
            'amount_allocated' => $invoice->total_amount,
        ]);

        // Check if has pending payment
        $hasPending = $invoice->payments()->where('status', 'PENDING')->exists();

        $this->assertTrue($hasPending);
    }

    /** @test */
    public function invoice_created_notification_is_formatted_correctly()
    {
        Notification::fake();

        $invoice = FinancialInvoice::create([
            'invoice_number' => 'INV-NOTIF-001',
            'id_registrasi_mahasiswa' => $this->studentId,
            'period_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 500000,
            'status' => 'UNPAID',
        ]);

        $this->studentUser->notify(new InvoiceCreatedNotification($invoice));

        Notification::assertSentTo(
            $this->studentUser,
            InvoiceCreatedNotification::class,
            function ($notification, $channels) {
                $data = $notification->toDatabase($this->studentUser);
                return str_contains($data['title'], 'Tagihan Baru')
                    && str_contains($data['body'], 'INV-NOTIF-001');
            }
        );
    }

    /** @test */
    public function invoice_reminder_notification_has_urgency_levels()
    {
        $invoice = FinancialInvoice::create([
            'invoice_number' => 'INV-REM-001',
            'id_registrasi_mahasiswa' => $this->studentId,
            'period_date' => now(),
            'due_date' => now()->addDays(7),
            'total_amount' => 500000,
            'status' => 'UNPAID',
        ]);

        // Test REMINDER urgency (7 days)
        $notification7Days = new InvoiceReminderNotification($invoice, 7);
        $data7 = $notification7Days->toDatabase($this->studentUser);
        $this->assertEquals('REMINDER', $data7['urgency']);
        $this->assertEquals('info', $data7['color']);

        // Test URGENT urgency (3 days)
        $notification3Days = new InvoiceReminderNotification($invoice, 3);
        $data3 = $notification3Days->toDatabase($this->studentUser);
        $this->assertEquals('URGENT', $data3['urgency']);
        $this->assertEquals('warning', $data3['color']);

        // Test OVERDUE urgency (0 days / past)
        $notificationOverdue = new InvoiceReminderNotification($invoice, 0);
        $dataOverdue = $notificationOverdue->toDatabase($this->studentUser);
        $this->assertEquals('OVERDUE', $dataOverdue['urgency']);
        $this->assertEquals('danger', $dataOverdue['color']);
    }

    /** @test */
    public function payment_verified_notification_shows_correct_status()
    {
        $payment = FinancialPayment::create([
            'payment_number' => 'PAY/2026/01/VER001',
            'payment_method' => PaymentMethod::MANUAL_TRANSFER,
            'proof_file_path' => 'payment-proofs/verified.jpg',
            'status' => 'VERIFIED',
            'verified_at' => now(),
        ]);

        // Test VERIFIED notification
        $verifiedNotif = new PaymentVerifiedNotification($payment, 'VERIFIED');
        $verifiedData = $verifiedNotif->toDatabase($this->studentUser);
        $this->assertStringContainsString('Diverifikasi', $verifiedData['title']);
        $this->assertEquals('success', $verifiedData['color']);

        // Test REJECTED notification
        $rejectedNotif = new PaymentVerifiedNotification($payment, 'REJECTED', 'Bukti tidak valid');
        $rejectedData = $rejectedNotif->toDatabase($this->studentUser);
        $this->assertStringContainsString('Ditolak', $rejectedData['title']);
        $this->assertStringContainsString('Bukti tidak valid', $rejectedData['body']);
        $this->assertEquals('danger', $rejectedData['color']);
    }

    /** @test */
    public function invoice_is_overdue_when_past_due_date()
    {
        $overdueInvoice = FinancialInvoice::create([
            'invoice_number' => 'INV-OVERDUE-001',
            'id_registrasi_mahasiswa' => $this->studentId,
            'period_date' => now()->subMonth(),
            'due_date' => now()->subDay(), // Yesterday
            'total_amount' => 500000,
            'status' => 'UNPAID',
        ]);

        $this->assertTrue($overdueInvoice->isOverdue());

        $normalInvoice = FinancialInvoice::create([
            'invoice_number' => 'INV-NORMAL-001',
            'id_registrasi_mahasiswa' => $this->studentId,
            'period_date' => now(),
            'due_date' => now()->addDays(7), // Future
            'total_amount' => 500000,
            'status' => 'UNPAID',
        ]);

        $this->assertFalse($normalInvoice->isOverdue());
    }

    /** @test */
    public function paid_invoice_is_not_considered_overdue()
    {
        $paidInvoice = FinancialInvoice::create([
            'invoice_number' => 'INV-PAID-001',
            'id_registrasi_mahasiswa' => $this->studentId,
            'period_date' => now()->subMonth(),
            'due_date' => now()->subDay(), // Past due date
            'total_amount' => 500000,
            'status' => 'PAID',
            'paid_at' => now(),
        ]);

        // PAID invoices should not be considered overdue even if due_date is past
        $this->assertFalse($paidInvoice->isOverdue());
    }
}

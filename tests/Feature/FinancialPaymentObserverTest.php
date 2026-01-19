<?php

namespace Tests\Feature;

use App\Enums\Finance\InvoiceStatus;
use App\Enums\Finance\PaymentMethod;
use App\Models\Finance\FinancialInvoice;
use App\Models\Finance\FinancialPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class FinancialPaymentObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_invoice_marked_paid_when_payment_verified(): void
    {
        // Arrange: Create invoice and payment
        $invoice = FinancialInvoice::factory()->create([
            'status' => InvoiceStatus::UNPAID,
            'total_amount' => 1000000,
        ]);

        $payment = FinancialPayment::factory()->create([
            'status' => 'PENDING',
            'payment_method' => PaymentMethod::MANUAL_TRANSFER,
        ]);

        // Link payment to invoice (cast UUID to string)
        $payment->invoices()->attach((string) $invoice->id, [
            'amount_allocated' => 1000000,
        ]);

        // Act: Update payment status to VERIFIED
        $payment->update([
            'status' => 'VERIFIED',
            'verified_at' => now(),
        ]);

        // Assert: Invoice should now be PAID
        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::PAID, $invoice->status);
        $this->assertNotNull($invoice->paid_at);
    }

    public function test_invoice_stays_unpaid_when_payment_rejected(): void
    {
        // Arrange
        $invoice = FinancialInvoice::factory()->create([
            'status' => InvoiceStatus::UNPAID,
            'total_amount' => 1000000,
        ]);

        $payment = FinancialPayment::factory()->create([
            'status' => 'PENDING',
            'payment_method' => PaymentMethod::MANUAL_TRANSFER,
        ]);

        // Link payment to invoice (cast UUID to string)
        $payment->invoices()->attach((string) $invoice->id, [
            'amount_allocated' => 1000000,
        ]);

        // Act: Reject the payment
        $payment->update([
            'status' => 'REJECTED',
            'notes' => 'Bukti tidak valid',
        ]);

        // Assert: Invoice should still be UNPAID
        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::UNPAID, $invoice->status);
        $this->assertNull($invoice->paid_at);
    }

    public function test_multiple_invoices_updated_on_single_payment_verification(): void
    {
        // Arrange: Create multiple invoices
        $invoice1 = FinancialInvoice::factory()->create([
            'status' => InvoiceStatus::UNPAID,
            'total_amount' => 500000,
        ]);

        $invoice2 = FinancialInvoice::factory()->create([
            'status' => InvoiceStatus::UNPAID,
            'total_amount' => 500000,
        ]);

        $payment = FinancialPayment::factory()->create([
            'status' => 'PENDING',
            'payment_method' => PaymentMethod::MANUAL_TRANSFER,
        ]);

        // Link payment to both invoices (one by one to avoid array key issues)
        $payment->invoices()->attach((string) $invoice1->id, ['amount_allocated' => 500000]);
        $payment->invoices()->attach((string) $invoice2->id, ['amount_allocated' => 500000]);

        // Act: Verify payment
        $payment->update(['status' => 'VERIFIED', 'verified_at' => now()]);

        // Assert: Both invoices should be PAID
        $invoice1->refresh();
        $invoice2->refresh();

        $this->assertEquals(InvoiceStatus::PAID, $invoice1->status);
        $this->assertEquals(InvoiceStatus::PAID, $invoice2->status);
    }
}

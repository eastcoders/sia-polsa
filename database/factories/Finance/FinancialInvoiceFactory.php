<?php

namespace Database\Factories\Finance;

use App\Enums\Finance\InvoiceStatus;
use App\Models\Finance\FinancialInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Finance\FinancialInvoice>
 */
class FinancialInvoiceFactory extends Factory
{
    protected $model = FinancialInvoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'invoice_number' => 'INV/' . date('Y/m/') . strtoupper($this->faker->bothify('###???')),
            'id_registrasi_mahasiswa' => Str::uuid(),
            'period_date' => now()->startOfMonth(),
            'due_date' => now()->addDays(30),
            'total_amount' => $this->faker->randomFloat(2, 500000, 5000000),
            'status' => InvoiceStatus::UNPAID,
            'generated_at' => now(),
        ];
    }

    /**
     * Indicate that the invoice is paid.
     */
    public function paid(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => InvoiceStatus::PAID,
            'paid_at' => now(),
        ]);
    }

    /**
     * Indicate that the invoice is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => InvoiceStatus::UNPAID,
            'due_date' => now()->subDays(7),
        ]);
    }
}

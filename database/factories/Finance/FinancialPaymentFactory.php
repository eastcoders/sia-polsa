<?php

namespace Database\Factories\Finance;

use App\Enums\Finance\PaymentMethod;
use App\Models\Finance\FinancialPayment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Finance\FinancialPayment>
 */
class FinancialPaymentFactory extends Factory
{
    protected $model = FinancialPayment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'payment_number' => 'PAY/' . date('Y/m/') . strtoupper($this->faker->bothify('###???')),
            'payment_method' => $this->faker->randomElement([
                PaymentMethod::MANUAL_TRANSFER,
                PaymentMethod::VIRTUAL_ACCOUNT,
                PaymentMethod::CASH,
            ]),
            'status' => 'PENDING',
            'proof_file_path' => null,
            'proof_file_hash' => null,
            'notes' => null,
        ];
    }

    /**
     * Indicate that the payment is verified.
     */
    public function verified(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'VERIFIED',
            'verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the payment is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'REJECTED',
            'notes' => 'Bukti pembayaran tidak valid',
        ]);
    }

    /**
     * Set payment as manual transfer with proof file.
     */
    public function manualTransfer(): static
    {
        return $this->state(fn(array $attributes) => [
            'payment_method' => PaymentMethod::MANUAL_TRANSFER,
            'proof_file_path' => 'payment-proofs/sample.jpg',
            'proof_file_hash' => hash('sha256', Str::random(32)),
        ]);
    }
}

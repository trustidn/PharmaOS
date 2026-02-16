<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(1000000, 100000000);
        $discount = fake()->optional(0.3)->numberBetween(0, (int) ($subtotal * 0.1)) ?? 0;
        $total = $subtotal - $discount;

        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'invoice_number' => 'INV-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'type' => TransactionType::Sale,
            'status' => TransactionStatus::Completed,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => 0,
            'total_amount' => $total,
            'payment_method' => fake()->randomElement(PaymentMethod::cases()),
            'amount_paid' => $total,
            'change_amount' => 0,
            'notes' => null,
            'completed_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Pending,
            'completed_at' => null,
        ]);
    }

    public function voided(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Voided,
        ]);
    }
}

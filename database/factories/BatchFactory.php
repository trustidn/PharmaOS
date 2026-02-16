<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Batch>
 */
class BatchFactory extends Factory
{
    protected $model = Batch::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(50, 500);

        return [
            'tenant_id' => Tenant::factory(),
            'product_id' => Product::factory(),
            'batch_number' => strtoupper(fake()->bothify('BATCH-####-??')),
            'purchase_price' => fake()->numberBetween(30000, 30000000),
            'quantity_received' => $quantity,
            'quantity_remaining' => $quantity,
            'expired_at' => fake()->dateTimeBetween('+3 months', '+2 years'),
            'received_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'is_active' => true,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expired_at' => fake()->dateTimeBetween('-6 months', '-1 day'),
        ]);
    }

    public function nearExpiry(int $days = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'expired_at' => now()->addDays($days),
        ]);
    }

    public function empty(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_remaining' => 0,
        ]);
    }
}

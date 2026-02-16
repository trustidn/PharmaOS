<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ProductUnit>
 */
class ProductUnitFactory extends Factory
{
    protected $model = \App\Models\ProductUnit::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::factory()->create();

        return [
            'tenant_id' => $product->tenant_id,
            'product_id' => $product->id,
            'name' => fake()->randomElement(['Strip', 'Box', 'Botol', 'Karton']),
            'conversion_factor' => fake()->randomElement([10, 12, 24, 100]),
            'price_sell' => fake()->numberBetween(100000, 50000000),
            'barcode' => fake()->optional(0.5)->ean13(),
        ];
    }
}

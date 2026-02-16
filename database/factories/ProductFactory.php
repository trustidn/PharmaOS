<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $medicines = [
            'Amoxicillin 500mg', 'Paracetamol 500mg', 'Ibuprofen 400mg',
            'Cetirizine 10mg', 'Omeprazole 20mg', 'Metformin 500mg',
            'Amlodipine 5mg', 'Captopril 25mg', 'Dexamethasone 0.5mg',
            'Ranitidine 150mg', 'Vitamin C 1000mg', 'Vitamin B Complex',
        ];

        return [
            'tenant_id' => Tenant::factory(),
            'category_id' => Category::factory(),
            'unit_id' => Unit::factory(),
            'base_unit' => 'pcs',
            'sku' => strtoupper(fake()->unique()->bothify('MED-####-??')),
            'barcode' => fake()->optional(0.7)->ean13(),
            'name' => fake()->randomElement($medicines),
            'generic_name' => fake()->optional()->word(),
            'description' => fake()->optional()->sentence(),
            'selling_price' => fake()->numberBetween(50000, 50000000),
            'min_stock' => fake()->numberBetween(5, 50),
            'requires_prescription' => fake()->boolean(30),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function prescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_prescription' => true,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->randomElement([
                'Obat Keras', 'Obat Bebas', 'Obat Bebas Terbatas',
                'Vitamin & Suplemen', 'Alat Kesehatan', 'Kosmetik',
                'Obat Herbal', 'Obat Generik',
            ]),
            'description' => fake()->optional()->sentence(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Apotek '.fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'owner_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'address' => fake()->address(),
            'logo_path' => null,
            'primary_color' => '#3B82F6',
            'secondary_color' => '#1E40AF',
            'license_number' => 'SIA-'.fake()->numerify('####/####'),
            'is_active' => true,
            'settings' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withBranding(): static
    {
        return $this->state(fn (array $attributes) => [
            'primary_color' => fake()->hexColor(),
            'secondary_color' => fake()->hexColor(),
        ]);
    }
}

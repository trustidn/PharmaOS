<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    protected $model = Unit::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $units = [
            ['name' => 'Tablet', 'abbreviation' => 'tab'],
            ['name' => 'Kapsul', 'abbreviation' => 'kap'],
            ['name' => 'Strip', 'abbreviation' => 'strip'],
            ['name' => 'Box', 'abbreviation' => 'box'],
            ['name' => 'Botol', 'abbreviation' => 'btl'],
            ['name' => 'Tube', 'abbreviation' => 'tube'],
            ['name' => 'Sachet', 'abbreviation' => 'sach'],
            ['name' => 'Ampul', 'abbreviation' => 'amp'],
        ];

        $unit = fake()->randomElement($units);

        return [
            'tenant_id' => Tenant::factory(),
            'name' => $unit['name'],
            'abbreviation' => $unit['abbreviation'],
        ];
    }
}

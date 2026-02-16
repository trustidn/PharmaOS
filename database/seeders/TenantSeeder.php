<?php

namespace Database\Seeders;

use App\Enums\SubscriptionPlan;
use App\Models\Category;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Seed demo tenants with users, categories, and units.
     */
    public function run(): void
    {
        // Super Admin
        User::factory()->superAdmin()->create([
            'name' => 'Super Admin',
            'email' => 'admin@pharmaos.test',
        ]);

        // Demo Tenant 1 - Apotek Sehat Selalu
        $tenant1 = Tenant::factory()->create([
            'name' => 'Apotek Sehat Selalu',
            'slug' => 'apotek-sehat-selalu',
            'owner_name' => 'Dr. Budi Santoso',
            'email' => 'info@apoteksehat.test',
        ]);

        Subscription::factory()
            ->plan(SubscriptionPlan::Pro)
            ->create(['tenant_id' => $tenant1->id]);

        User::factory()->owner()->create([
            'tenant_id' => $tenant1->id,
            'name' => 'Dr. Budi Santoso',
            'email' => 'budi@apoteksehat.test',
        ]);

        User::factory()->pharmacist()->create([
            'tenant_id' => $tenant1->id,
            'name' => 'Siti Apoteker',
            'email' => 'siti@apoteksehat.test',
        ]);

        User::factory()->cashier()->create([
            'tenant_id' => $tenant1->id,
            'name' => 'Rini Kasir',
            'email' => 'rini@apoteksehat.test',
        ]);

        $this->seedCategoriesAndUnits($tenant1);

        // Demo Tenant 2 - Apotek Medika Jaya
        $tenant2 = Tenant::factory()->create([
            'name' => 'Apotek Medika Jaya',
            'slug' => 'apotek-medika-jaya',
            'owner_name' => 'Apt. Dewi Lestari',
            'email' => 'info@medikajaya.test',
        ]);

        Subscription::factory()
            ->plan(SubscriptionPlan::Basic)
            ->create(['tenant_id' => $tenant2->id]);

        User::factory()->owner()->create([
            'tenant_id' => $tenant2->id,
            'name' => 'Apt. Dewi Lestari',
            'email' => 'dewi@medikajaya.test',
        ]);

        $this->seedCategoriesAndUnits($tenant2);
    }

    private function seedCategoriesAndUnits(Tenant $tenant): void
    {
        $categories = [
            ['name' => 'Obat Keras', 'description' => 'Obat yang hanya bisa dibeli dengan resep dokter'],
            ['name' => 'Obat Bebas Terbatas', 'description' => 'Obat yang dijual bebas terbatas'],
            ['name' => 'Obat Bebas', 'description' => 'Obat yang dijual bebas tanpa resep'],
            ['name' => 'Vitamin & Suplemen', 'description' => 'Vitamin dan suplemen kesehatan'],
            ['name' => 'Alat Kesehatan', 'description' => 'Alat-alat kesehatan'],
        ];

        foreach ($categories as $cat) {
            Category::create([
                'tenant_id' => $tenant->id,
                ...$cat,
            ]);
        }

        $units = [
            ['name' => 'Tablet', 'abbreviation' => 'tab'],
            ['name' => 'Kapsul', 'abbreviation' => 'kap'],
            ['name' => 'Strip', 'abbreviation' => 'strip'],
            ['name' => 'Box', 'abbreviation' => 'box'],
            ['name' => 'Botol', 'abbreviation' => 'btl'],
            ['name' => 'Tube', 'abbreviation' => 'tube'],
            ['name' => 'Sachet', 'abbreviation' => 'sach'],
        ];

        foreach ($units as $unit) {
            Unit::create([
                'tenant_id' => $tenant->id,
                ...$unit,
            ]);
        }
    }
}

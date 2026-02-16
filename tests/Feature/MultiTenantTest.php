<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Services\TenantContext;

beforeEach(function () {
    $this->tenant1 = Tenant::factory()->create(['name' => 'Apotek A']);
    $this->tenant2 = Tenant::factory()->create(['name' => 'Apotek B']);

    $this->unit1 = Unit::create(['tenant_id' => $this->tenant1->id, 'name' => 'Tablet', 'abbreviation' => 'tab']);
    $this->unit2 = Unit::create(['tenant_id' => $this->tenant2->id, 'name' => 'Tablet', 'abbreviation' => 'tab']);
});

test('tenant scope filters data by tenant_id', function () {
    Category::create(['tenant_id' => $this->tenant1->id, 'name' => 'Obat Keras']);
    Category::create(['tenant_id' => $this->tenant1->id, 'name' => 'Obat Bebas']);
    Category::create(['tenant_id' => $this->tenant2->id, 'name' => 'Vitamin']);

    $context = app(TenantContext::class);
    $context->setTenant($this->tenant1);

    expect(Category::count())->toBe(2);

    $context->setTenant($this->tenant2);

    expect(Category::count())->toBe(1);
});

test('tenant scope auto-fills tenant_id on creating', function () {
    $context = app(TenantContext::class);
    $context->setTenant($this->tenant1);

    $category = Category::create(['name' => 'Test Category']);

    expect($category->tenant_id)->toBe($this->tenant1->id);
});

test('super admin sees no scoped data when no tenant is set', function () {
    Category::create(['tenant_id' => $this->tenant1->id, 'name' => 'Cat A']);
    Category::create(['tenant_id' => $this->tenant2->id, 'name' => 'Cat B']);

    $context = app(TenantContext::class);
    $context->clear();

    expect(Category::count())->toBe(2);
});

test('product data is isolated between tenants', function () {
    $cat1 = Category::create(['tenant_id' => $this->tenant1->id, 'name' => 'Obat']);
    $cat2 = Category::create(['tenant_id' => $this->tenant2->id, 'name' => 'Obat']);

    Product::create([
        'tenant_id' => $this->tenant1->id,
        'category_id' => $cat1->id,
        'unit_id' => $this->unit1->id,
        'sku' => 'MED-001',
        'name' => 'Paracetamol',
        'selling_price' => 500000,
    ]);

    Product::create([
        'tenant_id' => $this->tenant2->id,
        'category_id' => $cat2->id,
        'unit_id' => $this->unit2->id,
        'sku' => 'MED-001',
        'name' => 'Amoxicillin',
        'selling_price' => 1000000,
    ]);

    $context = app(TenantContext::class);

    $context->setTenant($this->tenant1);
    expect(Product::count())->toBe(1)
        ->and(Product::first()->name)->toBe('Paracetamol');

    $context->setTenant($this->tenant2);
    expect(Product::count())->toBe(1)
        ->and(Product::first()->name)->toBe('Amoxicillin');
});

test('set tenant context middleware sets tenant for authenticated user', function () {
    $user = User::factory()->create([
        'tenant_id' => $this->tenant1->id,
    ]);

    $this->actingAs($user)->get(route('dashboard'));

    $context = app(TenantContext::class);

    expect($context->hasTenant())->toBeTrue()
        ->and($context->getTenantId())->toBe($this->tenant1->id);
});

test('super admin has no tenant context', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)->get(route('dashboard'));

    $context = app(TenantContext::class);

    expect($context->hasTenant())->toBeFalse();
});

test('inactive tenant returns 403', function () {
    $tenant = Tenant::factory()->inactive()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user)->get(route('dashboard'))->assertForbidden();
});

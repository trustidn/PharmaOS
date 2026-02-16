<?php

use App\Enums\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;

test('super admin cannot access tenant routes', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin);

    $this->get(route('inventory.products'))->assertForbidden();
    $this->get(route('pos.cashier'))->assertForbidden();
    $this->get(route('reports.sales'))->assertForbidden();
});

test('super admin can access admin routes', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin);

    $this->get(route('admin.tenants'))->assertOk();
    $this->get(route('admin.dashboard'))->assertOk();
});

test('tenant user cannot access admin routes', function () {
    $tenant = Tenant::factory()->create();
    Subscription::factory()->plan(SubscriptionPlan::Pro)->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->owner()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user);

    $this->get(route('admin.tenants'))->assertForbidden();
    $this->get(route('admin.dashboard'))->assertForbidden();
});

test('super admin sees only admin menu in sidebar', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSeeText('Kelola Tenant');
    $response->assertSeeText('System Dashboard');
    $response->assertDontSeeText('Kasir / POS');
    $response->assertDontSeeText('Produk / Obat');
});

test('tenant user sees tenant menus but not admin menu', function () {
    $tenant = Tenant::factory()->create();
    Subscription::factory()->plan(SubscriptionPlan::Pro)->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->owner()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSeeText('Kasir / POS');
    $response->assertSeeText('Produk / Obat');
    $response->assertDontSeeText('Kelola Tenant');
    $response->assertDontSeeText('System Dashboard');
});

test('only owner can access report routes', function () {
    $tenant = Tenant::factory()->create();
    Subscription::factory()->plan(SubscriptionPlan::Pro)->create(['tenant_id' => $tenant->id]);

    $owner = User::factory()->owner()->create(['tenant_id' => $tenant->id]);
    $this->actingAs($owner);
    $this->get(route('reports.sales'))->assertOk();
    $this->get(route('reports.stock'))->assertOk();
    $this->get(route('reports.expiry'))->assertOk();

    $cashier = User::factory()->cashier()->create(['tenant_id' => $tenant->id]);
    $this->actingAs($cashier);
    $this->get(route('reports.sales'))->assertForbidden();
    $this->get(route('reports.stock'))->assertForbidden();
    $this->get(route('reports.expiry'))->assertForbidden();

    $pharmacist = User::factory()->pharmacist()->create(['tenant_id' => $tenant->id]);
    $this->actingAs($pharmacist);
    $this->get(route('reports.sales'))->assertForbidden();
});

test('owner sees report menu in sidebar, cashier does not', function () {
    $tenant = Tenant::factory()->create();
    Subscription::factory()->plan(SubscriptionPlan::Pro)->create(['tenant_id' => $tenant->id]);

    $owner = User::factory()->owner()->create(['tenant_id' => $tenant->id]);
    $this->actingAs($owner);
    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSeeText('Penjualan');
    $response->assertSeeText('Stok');
    $response->assertSeeText('Kadaluarsa');

    $cashier = User::factory()->cashier()->create(['tenant_id' => $tenant->id]);
    $this->actingAs($cashier);
    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertDontSeeText('Laporan');
});

test('only owner can access white-label and user management routes', function () {
    $tenant = Tenant::factory()->create();
    Subscription::factory()->plan(SubscriptionPlan::Pro)->create(['tenant_id' => $tenant->id]);

    $owner = User::factory()->owner()->create(['tenant_id' => $tenant->id]);
    $this->actingAs($owner);
    $this->get(route('settings.white-label'))->assertOk();
    $this->get(route('settings.users'))->assertOk();

    $cashier = User::factory()->cashier()->create(['tenant_id' => $tenant->id]);
    $this->actingAs($cashier);
    $this->get(route('settings.white-label'))->assertForbidden();
    $this->get(route('settings.users'))->assertForbidden();
});

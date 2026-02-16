<?php

use App\Livewire\SuperAdmin\AppSettings;
use App\Livewire\SuperAdmin\SubscriptionManager;
use App\Livewire\SuperAdmin\SystemDashboard;
use App\Livewire\SuperAdmin\TenantForm;
use App\Livewire\SuperAdmin\TenantIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'super.admin'])->prefix('admin')->group(function () {
    Route::livewire('dashboard', SystemDashboard::class)->name('admin.dashboard');
    Route::livewire('settings', AppSettings::class)->name('admin.settings');
    Route::livewire('tenants', TenantIndex::class)->name('admin.tenants');
    Route::livewire('tenants/create', TenantForm::class)->name('admin.tenants.create');
    Route::livewire('tenants/{tenantId}/edit', TenantForm::class)->name('admin.tenants.edit');
    Route::livewire('tenants/{tenantId}/subscription', SubscriptionManager::class)->name('admin.tenants.subscription');
});

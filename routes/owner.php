<?php

use App\Livewire\Settings\BrandingSettings;
use App\Livewire\Settings\TenantUserManagement;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant.user', 'owner', 'subscription.active'])->group(function () {
    Route::livewire('settings/white-label', BrandingSettings::class)->name('settings.white-label');
    Route::livewire('settings/users', TenantUserManagement::class)->name('settings.users');
});

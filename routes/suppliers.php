<?php

use App\Livewire\Supplier\SupplierForm;
use App\Livewire\Supplier\SupplierIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant.user', 'subscription.active', 'check.feature:supplier_management'])->prefix('suppliers')->group(function () {
    Route::livewire('/', SupplierIndex::class)->name('suppliers.index');
    Route::livewire('create', SupplierForm::class)->name('suppliers.create');
    Route::livewire('{supplierId}/edit', SupplierForm::class)->name('suppliers.edit');
});

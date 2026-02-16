<?php

use App\Livewire\Inventory\BatchForm;
use App\Livewire\Inventory\BatchIndex;
use App\Livewire\Inventory\ProductForm;
use App\Livewire\Inventory\ProductIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant.user', 'subscription.active'])->prefix('inventory')->group(function () {
    // Products
    Route::livewire('products', ProductIndex::class)->name('inventory.products');
    Route::livewire('products/create', ProductForm::class)->name('inventory.products.create');
    Route::livewire('products/{productId}/edit', ProductForm::class)->name('inventory.products.edit');

    // Batches
    Route::livewire('products/{productId}/batches', BatchIndex::class)->name('inventory.batches');
    Route::livewire('products/{productId}/batches/create', BatchForm::class)->name('inventory.batches.create');
    Route::livewire('products/{productId}/batches/{batchId}/edit', BatchForm::class)->name('inventory.batches.edit');
});

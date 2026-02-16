<?php

use App\Livewire\Report\ExpiryReport;
use App\Livewire\Report\SalesReport;
use App\Livewire\Report\StockReport;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant.user', 'owner', 'subscription.active'])->prefix('reports')->group(function () {
    Route::livewire('sales', SalesReport::class)->name('reports.sales');
    Route::livewire('stock', StockReport::class)->name('reports.stock');
    Route::livewire('expiry', ExpiryReport::class)->name('reports.expiry');
});

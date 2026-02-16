<?php

use App\Livewire\POS\Cashier;
use App\Livewire\POS\TransactionHistory;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant.user', 'subscription.active'])->prefix('pos')->group(function () {
    Route::livewire('cashier', Cashier::class)->name('pos.cashier');
    Route::livewire('transactions', TransactionHistory::class)->name('pos.transactions');
});

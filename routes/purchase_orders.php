<?php

use App\Livewire\PurchaseOrder\PurchaseOrderDetail;
use App\Livewire\PurchaseOrder\PurchaseOrderForm;
use App\Livewire\PurchaseOrder\PurchaseOrderIndex;
use App\Livewire\PurchaseOrder\PurchaseOrderReceive;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant.user', 'subscription.active', 'check.feature:supplier_management'])
    ->prefix('purchase-orders')
    ->group(function () {
        Route::livewire('/', PurchaseOrderIndex::class)->name('purchase-orders.index');
        Route::livewire('create', PurchaseOrderForm::class)->name('purchase-orders.create');
        Route::livewire('{orderId}', PurchaseOrderDetail::class)->name('purchase-orders.show');
        Route::livewire('{orderId}/receive', PurchaseOrderReceive::class)->name('purchase-orders.receive');
    });

<?php

use App\Exceptions\InsufficientStockException;
use App\Models\Batch;
use App\Models\BatchDeduction;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Unit;
use App\Models\User;
use App\Services\StockService;
use App\Services\TenantContext;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    app(TenantContext::class)->setTenant($this->tenant);

    $this->unit = Unit::create(['tenant_id' => $this->tenant->id, 'name' => 'Tablet', 'abbreviation' => 'tab']);
    $this->category = Category::create(['tenant_id' => $this->tenant->id, 'name' => 'Obat']);

    $this->product = Product::create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $this->category->id,
        'unit_id' => $this->unit->id,
        'sku' => 'PARA-500',
        'name' => 'Paracetamol 500mg',
        'selling_price' => 500000,
    ]);

    $this->stockService = app(StockService::class);
});

test('FEFO deducts from earliest expiring batch first', function () {
    // Batch A: expires in 6 months, 100 units
    $batchA = Batch::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'batch_number' => 'BATCH-A',
        'purchase_price' => 300000,
        'quantity_received' => 100,
        'quantity_remaining' => 100,
        'expired_at' => now()->addMonths(6),
        'received_at' => now()->subMonth(),
    ]);

    // Batch B: expires in 3 months (should be deducted first), 50 units
    $batchB = Batch::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'batch_number' => 'BATCH-B',
        'purchase_price' => 350000,
        'quantity_received' => 50,
        'quantity_remaining' => 50,
        'expired_at' => now()->addMonths(3),
        'received_at' => now()->subWeeks(2),
    ]);

    $transaction = Transaction::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
    ]);

    $item = TransactionItem::create([
        'transaction_id' => $transaction->id,
        'product_id' => $this->product->id,
        'product_name' => $this->product->name,
        'quantity' => 30,
        'unit_price' => 500000,
        'subtotal' => 15000000,
    ]);

    $deductions = $this->stockService->deductFEFO(
        $this->product->id, 30, $item->id, $this->user->id
    );

    expect($deductions)->toHaveCount(1);

    // Batch B should be deducted first (earlier expiry)
    $batchB->refresh();
    $batchA->refresh();

    expect($batchB->quantity_remaining)->toBe(20)
        ->and($batchA->quantity_remaining)->toBe(100);
});

test('FEFO spans multiple batches when needed', function () {
    $batchB = Batch::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'batch_number' => 'BATCH-NEAR',
        'purchase_price' => 300000,
        'quantity_received' => 30,
        'quantity_remaining' => 30,
        'expired_at' => now()->addMonths(2),
        'received_at' => now()->subMonth(),
    ]);

    $batchA = Batch::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'batch_number' => 'BATCH-FAR',
        'purchase_price' => 350000,
        'quantity_received' => 100,
        'quantity_remaining' => 100,
        'expired_at' => now()->addMonths(8),
        'received_at' => now()->subWeeks(2),
    ]);

    $transaction = Transaction::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
    ]);

    $item = TransactionItem::create([
        'transaction_id' => $transaction->id,
        'product_id' => $this->product->id,
        'product_name' => $this->product->name,
        'quantity' => 50,
        'unit_price' => 500000,
        'subtotal' => 25000000,
    ]);

    $deductions = $this->stockService->deductFEFO(
        $this->product->id, 50, $item->id, $this->user->id
    );

    expect($deductions)->toHaveCount(2);

    $batchB->refresh();
    $batchA->refresh();

    // Batch B (near expiry) fully consumed, Batch A takes the rest
    expect($batchB->quantity_remaining)->toBe(0)
        ->and($batchA->quantity_remaining)->toBe(80);
});

test('FEFO throws exception when insufficient stock', function () {
    Batch::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'batch_number' => 'BATCH-LOW',
        'purchase_price' => 300000,
        'quantity_received' => 10,
        'quantity_remaining' => 10,
        'expired_at' => now()->addMonths(3),
        'received_at' => now()->subMonth(),
    ]);

    $transaction = Transaction::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
    ]);

    $item = TransactionItem::create([
        'transaction_id' => $transaction->id,
        'product_id' => $this->product->id,
        'product_name' => $this->product->name,
        'quantity' => 20,
        'unit_price' => 500000,
        'subtotal' => 10000000,
    ]);

    expect(fn () => $this->stockService->deductFEFO(
        $this->product->id, 20, $item->id, $this->user->id
    ))->toThrow(InsufficientStockException::class);
});

test('FEFO skips expired batches', function () {
    // Expired batch should be skipped
    Batch::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'batch_number' => 'BATCH-EXPIRED',
        'purchase_price' => 300000,
        'quantity_received' => 100,
        'quantity_remaining' => 100,
        'expired_at' => now()->subDay(),
        'received_at' => now()->subYear(),
    ]);

    $activeBatch = Batch::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'batch_number' => 'BATCH-ACTIVE',
        'purchase_price' => 300000,
        'quantity_received' => 50,
        'quantity_remaining' => 50,
        'expired_at' => now()->addMonths(6),
        'received_at' => now()->subMonth(),
    ]);

    $transaction = Transaction::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
    ]);

    $item = TransactionItem::create([
        'transaction_id' => $transaction->id,
        'product_id' => $this->product->id,
        'product_name' => $this->product->name,
        'quantity' => 10,
        'unit_price' => 500000,
        'subtotal' => 5000000,
    ]);

    $deductions = $this->stockService->deductFEFO(
        $this->product->id, 10, $item->id, $this->user->id
    );

    expect($deductions)->toHaveCount(1);

    $activeBatch->refresh();
    expect($activeBatch->quantity_remaining)->toBe(40);
});

test('FEFO creates stock movement records', function () {
    Batch::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'batch_number' => 'BATCH-X',
        'purchase_price' => 300000,
        'quantity_received' => 100,
        'quantity_remaining' => 100,
        'expired_at' => now()->addMonths(6),
        'received_at' => now()->subMonth(),
    ]);

    $transaction = Transaction::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
    ]);

    $item = TransactionItem::create([
        'transaction_id' => $transaction->id,
        'product_id' => $this->product->id,
        'product_name' => $this->product->name,
        'quantity' => 5,
        'unit_price' => 500000,
        'subtotal' => 2500000,
    ]);

    $this->stockService->deductFEFO(
        $this->product->id, 5, $item->id, $this->user->id
    );

    expect(StockMovement::count())->toBe(1);
    expect(StockMovement::first()->quantity)->toBe(-5);
    expect(BatchDeduction::count())->toBe(1);
    expect(BatchDeduction::first()->quantity_deducted)->toBe(5);
});

test('restore from deductions returns stock to batches', function () {
    $batch = Batch::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'batch_number' => 'BATCH-R',
        'purchase_price' => 300000,
        'quantity_received' => 100,
        'quantity_remaining' => 100,
        'expired_at' => now()->addMonths(6),
        'received_at' => now()->subMonth(),
    ]);

    $transaction = Transaction::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
    ]);

    $item = TransactionItem::create([
        'transaction_id' => $transaction->id,
        'product_id' => $this->product->id,
        'product_name' => $this->product->name,
        'quantity' => 20,
        'unit_price' => 500000,
        'subtotal' => 10000000,
    ]);

    $deductions = $this->stockService->deductFEFO(
        $this->product->id, 20, $item->id, $this->user->id
    );

    $batch->refresh();
    expect($batch->quantity_remaining)->toBe(80);

    $this->stockService->restoreFromDeductions($deductions, $this->user->id);

    $batch->refresh();
    expect($batch->quantity_remaining)->toBe(100);
});

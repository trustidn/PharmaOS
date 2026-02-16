<?php

use App\Actions\POS\ProcessSale;
use App\Actions\POS\VoidTransaction;
use App\Enums\PaymentMethod;
use App\Enums\SubscriptionPlan;
use App\Enums\TransactionStatus;
use App\Exceptions\InsufficientStockException;
use App\Models\Batch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\Unit;
use App\Models\User;
use App\Services\TenantContext;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    Subscription::factory()->plan(SubscriptionPlan::Pro)->create(['tenant_id' => $this->tenant->id]);

    $this->user = User::factory()->cashier()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($this->user);

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

    Batch::create([
        'tenant_id' => $this->tenant->id,
        'product_id' => $this->product->id,
        'batch_number' => 'BATCH-001',
        'purchase_price' => 300000,
        'quantity_received' => 100,
        'quantity_remaining' => 100,
        'expired_at' => now()->addMonths(6),
        'received_at' => now()->subMonth(),
    ]);
});

test('process sale creates transaction with items and deducts stock', function () {
    $action = app(ProcessSale::class);

    $transaction = $action->execute(
        items: [
            [
                'product_id' => $this->product->id,
                'quantity' => 5,
                'unit_price' => 500000,
                'discount' => 0,
            ],
        ],
        paymentMethod: PaymentMethod::Cash,
        amountPaid: 2500000,
    );

    expect($transaction)->toBeInstanceOf(Transaction::class)
        ->and($transaction->status)->toBe(TransactionStatus::Completed)
        ->and($transaction->total_amount)->toBe(2500000)
        ->and($transaction->items)->toHaveCount(1)
        ->and($transaction->items->first()->quantity)->toBe(5);

    // Stock should be reduced
    $batch = Batch::first();
    expect($batch->quantity_remaining)->toBe(95);
});

test('process sale calculates change correctly', function () {
    $action = app(ProcessSale::class);

    $transaction = $action->execute(
        items: [
            [
                'product_id' => $this->product->id,
                'quantity' => 2,
                'unit_price' => 500000,
                'discount' => 0,
            ],
        ],
        paymentMethod: PaymentMethod::Cash,
        amountPaid: 1500000,
    );

    expect($transaction->total_amount)->toBe(1000000)
        ->and($transaction->amount_paid)->toBe(1500000)
        ->and($transaction->change_amount)->toBe(500000);
});

test('process sale applies transaction discount', function () {
    $action = app(ProcessSale::class);

    $transaction = $action->execute(
        items: [
            [
                'product_id' => $this->product->id,
                'quantity' => 10,
                'unit_price' => 500000,
                'discount' => 0,
            ],
        ],
        paymentMethod: PaymentMethod::Cash,
        amountPaid: 4500000,
        discountAmount: 500000,
    );

    expect($transaction->subtotal)->toBe(5000000)
        ->and($transaction->discount_amount)->toBe(500000)
        ->and($transaction->total_amount)->toBe(4500000);
});

test('process sale stores optional buyer name and phone', function () {
    $action = app(ProcessSale::class);

    $transaction = $action->execute(
        items: [
            [
                'product_id' => $this->product->id,
                'quantity' => 1,
                'unit_price' => 500000,
                'discount' => 0,
            ],
        ],
        paymentMethod: PaymentMethod::Cash,
        amountPaid: 500000,
        buyerName: 'Budi Santoso',
        buyerPhone: '08123456789',
    );

    expect($transaction->buyer_name)->toBe('Budi Santoso')
        ->and($transaction->buyer_phone)->toBe('08123456789');
});

test('process sale fails with insufficient stock', function () {
    $action = app(ProcessSale::class);

    expect(fn () => $action->execute(
        items: [
            [
                'product_id' => $this->product->id,
                'quantity' => 200,
                'unit_price' => 500000,
                'discount' => 0,
            ],
        ],
        paymentMethod: PaymentMethod::Cash,
        amountPaid: 100000000,
    ))->toThrow(InsufficientStockException::class);

    // No transaction should be created (DB transaction rollback)
    expect(Transaction::count())->toBe(0);
});

test('void transaction restores stock and updates status', function () {
    $processAction = app(ProcessSale::class);
    $voidAction = app(VoidTransaction::class);

    $transaction = $processAction->execute(
        items: [
            [
                'product_id' => $this->product->id,
                'quantity' => 10,
                'unit_price' => 500000,
                'discount' => 0,
            ],
        ],
        paymentMethod: PaymentMethod::Cash,
        amountPaid: 5000000,
    );

    $batch = Batch::first();
    expect($batch->quantity_remaining)->toBe(90);

    $voidedTransaction = $voidAction->execute($transaction);

    expect($voidedTransaction->status)->toBe(TransactionStatus::Voided);

    $batch->refresh();
    expect($batch->quantity_remaining)->toBe(100);
});

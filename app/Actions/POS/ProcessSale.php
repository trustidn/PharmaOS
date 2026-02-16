<?php

namespace App\Actions\POS;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\PlanLimitService;
use App\Services\StockService;
use App\Services\TenantContext;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProcessSale
{
    public function __construct(
        private StockService $stockService,
        private TenantContext $tenantContext,
        private PlanLimitService $planLimitService,
    ) {}

    /**
     * Process a complete sale transaction.
     *
     * @param  array<int, array{product_id: int, quantity: int, unit_price: int, discount: int, unit_name?: string, conversion_factor?: int}>  $items
     *
     * @throws InsufficientStockException
     * @throws RuntimeException
     */
    public function execute(
        array $items,
        PaymentMethod $paymentMethod,
        int $amountPaid,
        int $discountAmount = 0,
        ?string $notes = null,
        ?string $buyerName = null,
        ?string $buyerPhone = null,
    ): Transaction {
        if (! $this->planLimitService->canCreateTransaction()) {
            throw new RuntimeException('Batas transaksi bulanan telah tercapai untuk paket Anda.');
        }

        return DB::transaction(function () use ($items, $paymentMethod, $amountPaid, $discountAmount, $notes, $buyerName, $buyerPhone) {
            $tenantId = $this->tenantContext->getTenantId();
            $userId = auth()->id();

            // Calculate totals
            $subtotal = 0;
            foreach ($items as $item) {
                $lineTotal = ($item['unit_price'] * $item['quantity']) - ($item['discount'] ?? 0);
                $subtotal += $lineTotal;
            }

            $totalAmount = $subtotal - $discountAmount;
            $changeAmount = max(0, $amountPaid - $totalAmount);

            // Create the transaction header
            $transaction = Transaction::create([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'invoice_number' => Transaction::generateInvoiceNumber($tenantId),
                'type' => TransactionType::Sale,
                'status' => TransactionStatus::Completed,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => 0,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'amount_paid' => $amountPaid,
                'change_amount' => $changeAmount,
                'notes' => $notes,
                'buyer_name' => $buyerName ? trim($buyerName) : null,
                'buyer_phone' => $buyerPhone ? trim($buyerPhone) : null,
                'completed_at' => now(),
            ]);

            // Create transaction items and deduct stock via FEFO (base unit quantity)
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $itemDiscount = $item['discount'] ?? 0;
                $itemSubtotal = ($item['unit_price'] * $item['quantity']) - $itemDiscount;
                $unitName = $item['unit_name'] ?? ($product->getAttribute('base_unit') ?? 'pcs');
                $conversionFactor = (int) ($item['conversion_factor'] ?? 1);
                $qtyToDeduct = $item['quantity'] * $conversionFactor;

                $transactionItem = TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_name' => $unitName,
                    'conversion_factor' => $conversionFactor,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $itemDiscount,
                    'subtotal' => $itemSubtotal,
                ]);

                $this->stockService->deductFEFO(
                    $product->id,
                    $qtyToDeduct,
                    $transactionItem->id,
                    $userId,
                );
            }

            return $transaction->load('items');
        });
    }
}

<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\Batch;
use App\Models\BatchDeduction;
use App\Models\StockMovement;
use App\Models\TransactionItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service that handles stock operations using FEFO (First Expired First Out) logic.
 */
class StockService
{
    /**
     * Deduct stock using FEFO strategy.
     *
     * Selects batches with the earliest expiry date first and deducts
     * the requested quantity across one or more batches.
     *
     * @param  int  $productId  The product to deduct stock from
     * @param  int  $quantity  The quantity to deduct
     * @param  int  $transactionItemId  The transaction item this deduction is for
     * @param  int  $userId  The user performing the operation
     * @return Collection<int, BatchDeduction> The batch deductions created
     *
     * @throws InsufficientStockException
     */
    public function deductFEFO(int $productId, int $quantity, int $transactionItemId, int $userId): Collection
    {
        return DB::transaction(function () use ($productId, $quantity, $transactionItemId, $userId) {
            $remainingNeed = $quantity;
            $deductions = collect();

            $batches = Batch::where('product_id', $productId)
                ->where('quantity_remaining', '>', 0)
                ->where('expired_at', '>', now()->toDateString())
                ->where('is_active', true)
                ->orderBy('expired_at', 'asc')
                ->lockForUpdate()
                ->get();

            foreach ($batches as $batch) {
                if ($remainingNeed <= 0) {
                    break;
                }

                $deductQty = min($batch->quantity_remaining, $remainingNeed);

                $batch->decrement('quantity_remaining', $deductQty);

                $deduction = BatchDeduction::create([
                    'transaction_item_id' => $transactionItemId,
                    'batch_id' => $batch->id,
                    'quantity_deducted' => $deductQty,
                ]);

                StockMovement::create([
                    'batch_id' => $batch->id,
                    'product_id' => $productId,
                    'type' => StockMovementType::Out,
                    'quantity' => -$deductQty,
                    'reference_type' => TransactionItem::class,
                    'reference_id' => $transactionItemId,
                    'created_by' => $userId,
                ]);

                $remainingNeed -= $deductQty;
                $deductions->push($deduction);
            }

            if ($remainingNeed > 0) {
                throw new InsufficientStockException(
                    "Stok tidak mencukupi. Kurang {$remainingNeed} unit."
                );
            }

            return $deductions;
        });
    }

    /**
     * Restore stock from batch deductions (used when voiding a transaction).
     *
     * @param  Collection<int, BatchDeduction>  $deductions
     */
    public function restoreFromDeductions(Collection $deductions, int $userId): void
    {
        DB::transaction(function () use ($deductions, $userId): void {
            foreach ($deductions as $deduction) {
                $batch = Batch::lockForUpdate()->find($deduction->batch_id);

                if ($batch) {
                    $batch->increment('quantity_remaining', $deduction->quantity_deducted);

                    StockMovement::create([
                        'batch_id' => $batch->id,
                        'product_id' => $batch->product_id,
                        'type' => StockMovementType::Return,
                        'quantity' => $deduction->quantity_deducted,
                        'reference_type' => TransactionItem::class,
                        'reference_id' => $deduction->transaction_item_id,
                        'notes' => 'Stok dikembalikan dari transaksi void',
                        'created_by' => $userId,
                    ]);
                }
            }
        });
    }

    /**
     * Add stock to a batch (used when receiving purchase orders).
     */
    public function addStock(Batch $batch, int $quantity, int $userId, ?string $notes = null): StockMovement
    {
        return DB::transaction(function () use ($batch, $quantity, $userId, $notes) {
            $batch->increment('quantity_remaining', $quantity);

            return StockMovement::create([
                'batch_id' => $batch->id,
                'product_id' => $batch->product_id,
                'type' => StockMovementType::In,
                'quantity' => $quantity,
                'notes' => $notes,
                'created_by' => $userId,
            ]);
        });
    }

    /**
     * Get total available stock for a product (non-expired, active batches).
     */
    public function getAvailableStock(int $productId): int
    {
        return Batch::where('product_id', $productId)
            ->where('is_active', true)
            ->where('expired_at', '>', now()->toDateString())
            ->where('quantity_remaining', '>', 0)
            ->sum('quantity_remaining');
    }
}

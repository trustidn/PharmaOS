<?php

namespace App\Actions\POS;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Services\StockService;
use RuntimeException;

class VoidTransaction
{
    public function __construct(
        private StockService $stockService,
    ) {}

    /**
     * Void a completed transaction and restore stock.
     */
    public function execute(Transaction $transaction): Transaction
    {
        if ($transaction->status !== TransactionStatus::Completed) {
            throw new RuntimeException('Hanya transaksi dengan status selesai yang bisa di-void.');
        }

        $transaction->load('items.batchDeductions');

        // Collect all batch deductions across all items
        $allDeductions = $transaction->items->flatMap(
            fn ($item) => $item->batchDeductions
        );

        // Restore stock
        $this->stockService->restoreFromDeductions($allDeductions, auth()->id());

        // Update transaction status
        $transaction->update(['status' => TransactionStatus::Voided]);

        return $transaction->refresh();
    }
}

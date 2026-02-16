<?php

namespace App\Livewire\POS;

use App\Actions\POS\VoidTransaction;
use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionHistory extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function voidTransaction(int $transactionId): void
    {
        $transaction = Transaction::findOrFail($transactionId);

        try {
            app(VoidTransaction::class)->execute($transaction);
            session()->flash('success', "Transaksi {$transaction->invoice_number} berhasil di-void.");
        } catch (\RuntimeException $e) {
            $this->addError('void', $e->getMessage());
        }
    }

    public function render()
    {
        $transactions = Transaction::query()
            ->with('cashier')
            ->when($this->search, fn ($q) => $q->where('invoice_number', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(20);

        return view('livewire.pos.transaction-history', [
            'transactions' => $transactions,
            'statuses' => TransactionStatus::cases(),
        ]);
    }
}

<?php

namespace App\Livewire\PurchaseOrder;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseOrderIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $supplierFilter = '';

    public string $statusFilter = '';

    public string $period = 'all';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function mount(): void
    {
        $this->applyPeriod();
    }

    public function updatedPeriod(): void
    {
        $this->applyPeriod();
        $this->resetPage();
    }

    private function applyPeriod(): void
    {
        if ($this->period === 'today') {
            $this->dateFrom = $this->dateTo = today()->format('Y-m-d');
        } elseif ($this->period === 'month') {
            $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
            $this->dateTo = now()->endOfMonth()->format('Y-m-d');
        } elseif ($this->period === 'year') {
            $this->dateFrom = now()->startOfYear()->format('Y-m-d');
            $this->dateTo = now()->endOfYear()->format('Y-m-d');
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function deleteOrder(int $orderId): void
    {
        $order = PurchaseOrder::findOrFail($orderId);

        if ($order->received_at !== null) {
            session()->flash('error', 'PO yang sudah diterima tidak dapat dihapus.');

            return;
        }

        $order->delete();
        session()->flash('success', 'Purchase Order berhasil dibatalkan dan dihapus.');
    }

    public function render()
    {
        $baseQuery = PurchaseOrder::query()
            ->when($this->search, fn ($q) => $q->where('invoice_number', 'like', "%{$this->search}%"))
            ->when($this->supplierFilter, fn ($q) => $q->where('supplier_id', $this->supplierFilter))
            ->when($this->statusFilter === 'received', fn ($q) => $q->whereNotNull('received_at'))
            ->when($this->statusFilter === 'pending', fn ($q) => $q->whereNull('received_at'))
            ->when($this->period !== 'all' && $this->dateFrom, fn ($q) => $q->whereDate('ordered_at', '>=', $this->dateFrom))
            ->when($this->period !== 'all' && $this->dateTo, fn ($q) => $q->whereDate('ordered_at', '<=', $this->dateTo));

        $totalAmount = (clone $baseQuery)->sum('total_amount');
        $orders = (clone $baseQuery)->with(['supplier', 'creator'])->latest()->paginate(15);
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('livewire.purchase-order.purchase-order-index', [
            'orders' => $orders,
            'suppliers' => $suppliers,
            'totalAmount' => $totalAmount,
        ]);
    }
}

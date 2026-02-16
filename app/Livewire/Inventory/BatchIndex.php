<?php

namespace App\Livewire\Inventory;

use App\Models\Batch;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class BatchIndex extends Component
{
    use WithPagination;

    public Product $product;

    public string $search = '';

    public string $statusFilter = '';

    public function mount(int $productId): void
    {
        $this->product = Product::with('unit')->findOrFail($productId);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function deactivateBatch(int $batchId): void
    {
        $batch = Batch::findOrFail($batchId);
        $batch->update(['is_active' => false]);
    }

    public function render()
    {
        $batches = Batch::query()
            ->where('product_id', $this->product->id)
            ->with('purchaseOrderItem')
            ->when($this->search, fn ($q) => $q->where('batch_number', 'like', "%{$this->search}%"))
            ->when($this->statusFilter === 'expired', fn ($q) => $q->where('expired_at', '<=', now()))
            ->when($this->statusFilter === 'near_expiry', fn ($q) => $q->where('expired_at', '>', now())->where('expired_at', '<=', now()->addDays(90)))
            ->when($this->statusFilter === 'active', fn ($q) => $q->where('expired_at', '>', now())->where('is_active', true)->where('quantity_remaining', '>', 0))
            ->latest('expired_at')
            ->paginate(15);

        return view('livewire.inventory.batch-index', [
            'batches' => $batches,
        ]);
    }
}

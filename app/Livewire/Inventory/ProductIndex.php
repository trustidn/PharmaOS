<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $categoryFilter = '';

    public string $stockFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function deleteProduct(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $product->update(['is_active' => false]);

        $this->dispatch('product-deactivated');
    }

    public function render()
    {
        $products = Product::query()
            ->with(['category', 'unit'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('sku', 'like', "%{$this->search}%")
                        ->orWhere('barcode', 'like', "%{$this->search}%")
                        ->orWhere('generic_name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->categoryFilter, fn ($q) => $q->where('category_id', $this->categoryFilter))
            ->when($this->stockFilter === 'low', function ($query) {
                $query->whereHas('batches', function ($q) {
                    $q->where('is_active', true)
                        ->where('expired_at', '>', now())
                        ->where('quantity_remaining', '>', 0);
                }, '<', 1)->orWhereColumn(
                    \Illuminate\Support\Facades\DB::raw('(SELECT COALESCE(SUM(quantity_remaining), 0) FROM batches WHERE batches.product_id = products.id AND batches.is_active = 1 AND batches.expired_at > NOW() AND batches.quantity_remaining > 0)'),
                    '<=',
                    'products.min_stock'
                );
            })
            ->where('is_active', true)
            ->latest()
            ->paginate(15);

        $categories = Category::orderBy('name')->get();

        return view('livewire.inventory.product-index', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}

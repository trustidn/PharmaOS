<?php

namespace App\Livewire\Report;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class StockReport extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filter = '';

    public bool $showStockDetailModal = false;

    public ?int $detailProductId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openStockDetail(int $productId): void
    {
        $this->detailProductId = $productId;
        $this->showStockDetailModal = true;
    }

    public function closeStockDetail(): void
    {
        $this->showStockDetailModal = false;
        $this->detailProductId = null;
    }

    public function render()
    {
        $products = Product::query()
            ->with(['unit', 'category'])
            ->withSum([
                'batches as total_stock' => function ($q) {
                    $q->where('is_active', true)
                        ->where('expired_at', '>', now())
                        ->where('quantity_remaining', '>', 0);
                },
            ], 'quantity_remaining')
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%");
            })
            ->when($this->filter === 'low', function ($q) {
                $q->having('total_stock', '<=', DB::raw('products.min_stock'));
            })
            ->when($this->filter === 'empty', function ($q) {
                $q->having('total_stock', '=', 0)->orHavingNull('total_stock');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(20);

        $summaryQuery = Product::query()->where('is_active', true);
        $totalProducts = (clone $summaryQuery)->count();
        $lowStockCount = Product::query()
            ->where('is_active', true)
            ->withSum([
                'batches as available_stock' => function ($q) {
                    $q->where('is_active', true)
                        ->where('expired_at', '>', now())
                        ->where('quantity_remaining', '>', 0);
                },
            ], 'quantity_remaining')
            ->get()
            ->filter(fn ($p) => ($p->available_stock ?? 0) <= $p->min_stock)
            ->count();

        $detailProduct = null;
        $detailBatches = collect();
        if ($this->detailProductId) {
            $detailProduct = Product::with('unit')->find($this->detailProductId);
            if ($detailProduct) {
                $detailBatches = Batch::query()
                    ->where('product_id', $this->detailProductId)
                    ->availableForSale()
                    ->orderBy('expired_at')
                    ->get();
            }
        }

        return view('livewire.report.stock-report', [
            'products' => $products,
            'totalProducts' => $totalProducts,
            'lowStockCount' => $lowStockCount,
            'detailProduct' => $detailProduct,
            'detailBatches' => $detailBatches,
        ]);
    }
}

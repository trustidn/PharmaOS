<?php

namespace App\Livewire\Report;

use App\Models\Batch;
use Livewire\Component;
use Livewire\WithPagination;

class ExpiryReport extends Component
{
    use WithPagination;

    public string $search = '';

    public int $daysThreshold = 90;

    public string $filter = 'near_expiry';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Batch::query()
            ->with(['product.unit', 'product.category'])
            ->where('is_active', true)
            ->where('quantity_remaining', '>', 0)
            ->when($this->search, function ($q) {
                $q->whereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%"));
            });

        if ($this->filter === 'expired') {
            $query->where('expired_at', '<=', now());
        } elseif ($this->filter === 'near_expiry') {
            $query->where('expired_at', '>', now())
                ->where('expired_at', '<=', now()->addDays($this->daysThreshold));
        }

        $batches = $query->orderBy('expired_at', 'asc')->paginate(20);

        $expiredCount = Batch::where('is_active', true)
            ->where('quantity_remaining', '>', 0)
            ->where('expired_at', '<=', now())
            ->count();

        $nearExpiryCount = Batch::where('is_active', true)
            ->where('quantity_remaining', '>', 0)
            ->where('expired_at', '>', now())
            ->where('expired_at', '<=', now()->addDays($this->daysThreshold))
            ->count();

        return view('livewire.report.expiry-report', [
            'batches' => $batches,
            'expiredCount' => $expiredCount,
            'nearExpiryCount' => $nearExpiryCount,
        ]);
    }
}

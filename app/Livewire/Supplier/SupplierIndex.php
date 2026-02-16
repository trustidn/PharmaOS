<?php

namespace App\Livewire\Supplier;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleActive(int $supplierId): void
    {
        $supplier = Supplier::findOrFail($supplierId);
        $supplier->update(['is_active' => ! $supplier->is_active]);
    }

    public function render()
    {
        $suppliers = Supplier::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('contact_person', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(15);

        return view('livewire.supplier.supplier-index', [
            'suppliers' => $suppliers,
        ]);
    }
}

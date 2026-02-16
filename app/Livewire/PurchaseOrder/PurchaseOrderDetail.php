<?php

namespace App\Livewire\PurchaseOrder;

use App\Models\PurchaseOrder;
use Livewire\Component;

class PurchaseOrderDetail extends Component
{
    public PurchaseOrder $order;

    public function mount(int $orderId): void
    {
        $this->order = PurchaseOrder::with(['supplier', 'creator', 'items.product.unit', 'items.batch'])
            ->findOrFail($orderId);
    }

    public function render()
    {
        return view('livewire.purchase-order.purchase-order-detail');
    }
}

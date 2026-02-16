<?php

namespace App\Livewire\PurchaseOrder;

use App\Enums\StockMovementType;
use App\Models\Batch;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PurchaseOrderReceive extends Component
{
    public PurchaseOrder $order;

    /** @var array<int, array{batch_number: string, expired_at: string}> */
    public array $itemData = [];

    public string $received_at = '';

    public function mount(int $orderId): void
    {
        $this->order = PurchaseOrder::with(['items.product', 'supplier'])->findOrFail($orderId);

        if ($this->order->isReceived()) {
            session()->flash('error', 'PO ini sudah diterima.');
            $this->redirect(route('purchase-orders.index'), navigate: true);

            return;
        }

        $this->received_at = now()->format('Y-m-d');

        foreach ($this->order->items as $item) {
            $this->itemData[$item->id] = [
                'batch_number' => 'BATCH-'.strtoupper(substr(uniqid(), -6)),
                'expired_at' => now()->addMonths(12)->format('Y-m-d'),
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'received_at' => ['required', 'date'],
        ];

        foreach ($this->order->items as $item) {
            $rules["itemData.{$item->id}.batch_number"] = ['required', 'string', 'max:100'];
            $rules["itemData.{$item->id}.expired_at"] = ['required', 'date', 'after:today'];
        }

        return $rules;
    }

    public function receive(): void
    {
        $this->validate();

        DB::transaction(function (): void {
            $receivedAt = $this->received_at;
            $userId = auth()->id();

            foreach ($this->order->items as $item) {
                $data = $this->itemData[$item->id] ?? [];
                $batchNumber = $data['batch_number'] ?? 'BATCH-'.uniqid();
                $expiredAt = $data['expired_at'] ?? now()->addYear()->format('Y-m-d');

                $quantityBase = $item->quantityInBaseUnit();
                $factor = max(1, $item->conversion_factor);
                $purchasePricePerBaseCents = (int) round($item->unit_price / $factor);

                $batch = Batch::create([
                    'tenant_id' => $this->order->tenant_id,
                    'product_id' => $item->product_id,
                    'batch_number' => $batchNumber,
                    'purchase_price' => $purchasePricePerBaseCents,
                    'quantity_received' => $quantityBase,
                    'quantity_remaining' => $quantityBase,
                    'expired_at' => $expiredAt,
                    'received_at' => $receivedAt,
                    'is_active' => true,
                ]);

                $item->update(['batch_id' => $batch->id]);

                StockMovement::create([
                    'tenant_id' => $this->order->tenant_id,
                    'batch_id' => $batch->id,
                    'product_id' => $item->product_id,
                    'type' => StockMovementType::In,
                    'quantity' => $quantityBase,
                    'reference_type' => PurchaseOrder::class,
                    'reference_id' => $this->order->id,
                    'notes' => 'Penerimaan PO '.$this->order->invoice_number,
                    'created_by' => $userId,
                ]);
            }

            $this->order->update(['received_at' => $receivedAt]);
        });

        session()->flash('success', 'Barang berhasil diterima. Stok telah ditambahkan.');

        $this->redirect(route('purchase-orders.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.purchase-order.purchase-order-receive');
    }
}

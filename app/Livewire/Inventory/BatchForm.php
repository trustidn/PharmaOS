<?php

namespace App\Livewire\Inventory;

use App\Enums\StockMovementType;
use App\Models\Batch;
use App\Models\Product;
use App\Models\StockMovement;
use Livewire\Component;

class BatchForm extends Component
{
    public Product $product;

    public ?Batch $batch = null;

    public string $batch_number = '';

    /** Harga beli dalam Rupiah (input dengan koma desimal) */
    public string $purchase_price_rupiah = '0';

    public int $quantity_received = 0;

    public string $expired_at = '';

    public string $received_at = '';

    public function mount(int $productId, ?int $batchId = null): void
    {
        $this->product = Product::findOrFail($productId);

        if ($batchId) {
            $this->batch = Batch::findOrFail($batchId);
            $this->fill($this->batch->only([
                'batch_number', 'quantity_received',
            ]));
            $this->purchase_price_rupiah = format_rupiah_input($this->batch->purchase_price);
            $this->expired_at = $this->batch->expired_at->format('Y-m-d');
            $this->received_at = $this->batch->received_at->format('Y-m-d');
        } else {
            $this->received_at = now()->format('Y-m-d');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'batch_number' => ['required', 'string', 'max:100'],
            'purchase_price_rupiah' => ['required', 'string'],
            'quantity_received' => ['required', 'integer', 'min:1'],
            'expired_at' => ['required', 'date', 'after:today'],
            'received_at' => ['required', 'date'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $purchasePriceCents = (int) round(parse_money_to_float($this->purchase_price_rupiah) * 100);
        if ($purchasePriceCents < 0) {
            $this->addError('purchase_price_rupiah', 'Harga beli harus tidak negatif.');

            return;
        }

        if ($this->batch) {
            $this->batch->update([
                'batch_number' => $this->batch_number,
                'purchase_price' => $purchasePriceCents,
                'expired_at' => $this->expired_at,
                'received_at' => $this->received_at,
            ]);

            session()->flash('success', 'Batch berhasil diperbarui.');
        } else {
            $batch = Batch::create([
                'product_id' => $this->product->id,
                'batch_number' => $this->batch_number,
                'purchase_price' => $purchasePriceCents,
                'quantity_received' => $this->quantity_received,
                'quantity_remaining' => $this->quantity_received,
                'expired_at' => $this->expired_at,
                'received_at' => $this->received_at,
            ]);

            StockMovement::create([
                'batch_id' => $batch->id,
                'product_id' => $this->product->id,
                'type' => StockMovementType::In,
                'quantity' => $this->quantity_received,
                'notes' => "Batch baru: {$this->batch_number}",
                'created_by' => auth()->id(),
            ]);

            session()->flash('success', 'Batch berhasil ditambahkan.');
        }

        $this->redirect(
            route('inventory.batches', $this->product),
            navigate: true,
        );
    }

    public function render()
    {
        return view('livewire.inventory.batch-form');
    }
}

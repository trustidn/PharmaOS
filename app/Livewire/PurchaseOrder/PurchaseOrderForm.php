<?php

namespace App\Livewire\PurchaseOrder;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Livewire\Component;

class PurchaseOrderForm extends Component
{
    public string $supplier_id = '';

    public string $ordered_at = '';

    public string $notes = '';

    /**
     * Item: product_id, product_name, order_unit_key ('base'|ProductUnit id), order_unit_name, conversion_factor,
     * quantity (in order unit), unit_price (Rupiah per order unit), subtotal.
     *
     * @var array<int, array{product_id: string, product_name: string, order_unit_key: string, order_unit_name: string, conversion_factor: int, quantity: string, unit_price: string, subtotal: int}>
     */
    public array $items = [];

    public function mount(): void
    {
        $this->ordered_at = now()->format('Y-m-d');
        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => '',
            'product_name' => '',
            'order_unit_key' => 'base',
            'order_unit_name' => 'pcs',
            'conversion_factor' => 1,
            'quantity' => '1',
            'unit_price' => '0',
            'subtotal' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, string $key): void
    {
        if (preg_match('/^items\.(\d+)\.product_id$/', $key, $m)) {
            $index = (int) $m[1];
            $productId = (int) ($this->items[$index]['product_id'] ?? 0);
            if ($productId) {
                $this->selectProduct($index, $productId);
            }
        } elseif (preg_match('/^items\.(\d+)\.order_unit_key$/', $key, $m)) {
            $this->changeOrderUnit((int) $m[1], (string) $value);
        } else {
            $this->recalculateSubtotals();
        }
    }

    /**
     * Recalculate subtotals. unit_price = per order unit (Rupiah), quantity = in order unit.
     */
    private function recalculateSubtotals(): void
    {
        foreach ($this->items as $index => $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $priceRupiah = parse_money_to_float($item['unit_price'] ?? '');
            $this->items[$index]['subtotal'] = (int) round($qty * $priceRupiah);
        }
    }

    public function selectProduct(int $index, int $productId): void
    {
        $product = Product::with(['unit', 'productUnits'])->find($productId);
        if ($product && isset($this->items[$index])) {
            $baseUnit = $product->getAttribute('base_unit') ?? 'pcs';
            $lastPurchasePriceCents = $product->batches()->latest('received_at')->value('purchase_price');
            $defaultPriceCents = (int) ($product->selling_price * 0.6);
            $priceRupiah = $lastPurchasePriceCents !== null
                ? (int) round($lastPurchasePriceCents / 100)
                : (int) round($defaultPriceCents / 100);

            $this->items[$index]['product_id'] = (string) $product->id;
            $this->items[$index]['product_name'] = $product->name.' ('.$product->unit->abbreviation.')';
            $this->items[$index]['order_unit_key'] = 'base';
            $this->items[$index]['order_unit_name'] = $baseUnit;
            $this->items[$index]['conversion_factor'] = 1;
            $this->items[$index]['unit_price'] = (string) $priceRupiah;
            $this->recalculateSubtotals();
        }
    }

    /**
     * Set order unit for item (base or a product_unit). Updates order_unit_name and conversion_factor.
     */
    public function changeOrderUnit(int $index, string $orderUnitKey): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $productId = (int) ($this->items[$index]['product_id'] ?? 0);
        if (! $productId) {
            return;
        }

        $product = Product::with('productUnits')->find($productId);
        if (! $product) {
            return;
        }

        $baseUnit = $product->getAttribute('base_unit') ?? 'pcs';

        if ($orderUnitKey === 'base') {
            $this->items[$index]['order_unit_key'] = 'base';
            $this->items[$index]['order_unit_name'] = $baseUnit;
            $this->items[$index]['conversion_factor'] = 1;
        } else {
            $pu = $product->productUnits->firstWhere('id', (int) $orderUnitKey);
            if ($pu) {
                $this->items[$index]['order_unit_key'] = (string) $pu->id;
                $this->items[$index]['order_unit_name'] = $pu->name;
                $this->items[$index]['conversion_factor'] = $pu->conversion_factor;
            }
        }

        $this->recalculateSubtotals();
    }

    /**
     * Resolve order_unit_name and conversion_factor from item (and order_unit_key) so save always persists the correct unit.
     *
     * @param  array<string, mixed>  $item
     * @return array{0: string, 1: int}
     */
    private function resolveOrderUnitForItem(array $item): array
    {
        $key = $item['order_unit_key'] ?? 'base';
        $productId = (int) ($item['product_id'] ?? 0);

        if ($key === 'base' || $key === '' || ! $productId) {
            return [$item['order_unit_name'] ?? 'pcs', (int) ($item['conversion_factor'] ?? 1)];
        }

        $product = Product::with('productUnits')->find($productId);
        if (! $product) {
            return [$item['order_unit_name'] ?? 'pcs', (int) ($item['conversion_factor'] ?? 1)];
        }

        $pu = $product->productUnits->firstWhere('id', (int) $key);
        if ($pu) {
            return [$pu->name, $pu->conversion_factor];
        }

        return [$item['order_unit_name'] ?? $product->base_unit ?? 'pcs', (int) ($item['conversion_factor'] ?? 1)];
    }

    /**
     * Total amount in Rupiah (for display). Convert to cents when saving.
     */
    public function getTotalAmountProperty(): int
    {
        return (int) array_sum(array_column($this->items, 'subtotal'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'ordered_at' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.order_unit_name' => ['required', 'string', 'max:100'],
            'items.*.conversion_factor' => ['required', 'integer', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $tenantId = auth()->user()->tenant_id;
        if (! $tenantId) {
            return;
        }

        $totalAmountCents = 0;

        foreach ($this->items as $item) {
            $qty = (int) $item['quantity'];
            $priceRupiah = parse_money_to_float($item['unit_price'] ?? '');
            $subtotalRupiah = $qty * $priceRupiah;
            $totalAmountCents += (int) round($subtotalRupiah * 100);
        }

        $order = PurchaseOrder::create([
            'tenant_id' => $tenantId,
            'supplier_id' => $this->supplier_id,
            'invoice_number' => PurchaseOrder::generateInvoiceNumber($tenantId),
            'total_amount' => $totalAmountCents,
            'notes' => $this->notes ?: null,
            'ordered_at' => $this->ordered_at,
            'created_by' => auth()->id(),
        ]);

        foreach ($this->items as $item) {
            $qty = (int) $item['quantity'];
            $priceRupiah = parse_money_to_float($item['unit_price'] ?? '');
            $unitPriceCents = (int) round($priceRupiah * 100);
            $subtotalCents = (int) round($qty * $priceRupiah * 100);

            [$orderUnitName, $conversionFactor] = $this->resolveOrderUnitForItem($item);

            PurchaseOrderItem::create([
                'purchase_order_id' => $order->id,
                'product_id' => $item['product_id'],
                'order_unit_name' => $orderUnitName,
                'conversion_factor' => $conversionFactor,
                'quantity' => $qty,
                'unit_price' => $unitPriceCents,
                'subtotal' => $subtotalCents,
            ]);
        }

        session()->flash('success', 'Purchase Order berhasil dibuat. Lakukan penerimaan barang untuk menambah stok.');

        $this->redirect(route('purchase-orders.receive', $order), navigate: true);
    }

    public function render()
    {
        return view('livewire.purchase-order.purchase-order-form', [
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
            'products' => Product::where('is_active', true)->with(['unit', 'productUnits'])->orderBy('name')->get(),
        ]);
    }
}

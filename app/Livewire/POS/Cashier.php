<?php

namespace App\Livewire\POS;

use App\Actions\POS\ProcessSale;
use App\Enums\PaymentMethod;
use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Services\BrandingService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class Cashier extends Component
{
    public string $search = '';

    /**
     * Cart item: product_id, name, sku, quantity, unit_price (cents), discount, subtotal,
     * unit_name (e.g. Butir, Strip), conversion_factor (1 = base).
     *
     * @var array<int, array{product_id: int, name: string, sku: string, quantity: int, unit_price: int, discount: int, subtotal: int, unit_name: string, conversion_factor: int}>
     */
    public array $cart = [];

    public int $discountAmount = 0;

    public string $paymentMethod = 'cash';

    public int $amountPaid = 0;

    public string $notes = '';

    public string $buyerName = '';

    public string $buyerPhone = '';

    public bool $showPaymentModal = false;

    public ?array $lastTransaction = null;

    /** @var array<int, array<string, mixed>> */
    public array $searchResults = [];

    public bool $showUnitModal = false;

    /** @var array<string, mixed>|null Product for unit picker (id, name, base_unit, selling_price, product_units). */
    public ?array $selectedProductForUnit = null;

    public string $selectedUnitKey = 'base';

    public int $unitQuantityToAdd = 1;

    public function updatedSearch(): void
    {
        if (strlen($this->search) < 2) {
            $this->searchResults = [];

            return;
        }

        $products = Product::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%")
                    ->orWhere('barcode', $this->search);
            })
            ->with(['unit', 'productUnits'])
            ->limit(10)
            ->get();

        $this->searchResults = $products->map(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'selling_price' => $p->selling_price,
            'base_unit' => $p->getAttribute('base_unit') ?? 'pcs',
            'product_units' => $p->productUnits->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'conversion_factor' => $u->conversion_factor,
                'price_sell' => $u->price_sell,
            ])->values()->all(),
        ])->all();
    }

    /**
     * Called when user clicks a product in search. Opens unit picker if product has multiple units.
     */
    public function selectProductForCart(int $productId): void
    {
        $product = Product::with('productUnits')->find($productId);

        if (! $product) {
            return;
        }

        $baseUnit = $product->getAttribute('base_unit') ?? 'pcs';

        if ($product->productUnits->isEmpty()) {
            $this->addToCartWithUnit(
                $product->id,
                $product->name,
                $product->sku,
                $baseUnit,
                1,
                $product->selling_price,
                1
            );
            $this->search = '';
            $this->searchResults = [];

            return;
        }

        $this->selectedProductForUnit = [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'base_unit' => $baseUnit,
            'selling_price' => $product->selling_price,
            'product_units' => $product->productUnits->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'conversion_factor' => $u->conversion_factor,
                'price_sell' => $u->price_sell,
            ])->values()->all(),
        ];
        $this->selectedUnitKey = 'base';
        $this->unitQuantityToAdd = 1;
        $this->showUnitModal = true;
    }

    public function addToCartWithUnitFromModal(): void
    {
        if (! $this->selectedProductForUnit) {
            return;
        }

        $unitName = $this->selectedProductForUnit['base_unit'];
        $conversionFactor = 1;
        $unitPrice = $this->selectedProductForUnit['selling_price'];

        if ($this->selectedUnitKey !== 'base') {
            foreach ($this->selectedProductForUnit['product_units'] ?? [] as $pu) {
                if ((string) $pu['id'] === (string) $this->selectedUnitKey) {
                    $unitName = $pu['name'];
                    $conversionFactor = $pu['conversion_factor'];
                    $unitPrice = $pu['price_sell'];
                    break;
                }
            }
        }

        $this->addToCartWithUnit(
            $this->selectedProductForUnit['id'],
            $this->selectedProductForUnit['name'],
            $this->selectedProductForUnit['sku'],
            $unitName,
            $conversionFactor,
            $unitPrice,
            $this->unitQuantityToAdd
        );

        $this->showUnitModal = false;
        $this->selectedProductForUnit = null;
        $this->search = '';
        $this->searchResults = [];
    }

    public function closeUnitModal(): void
    {
        $this->showUnitModal = false;
        $this->selectedProductForUnit = null;
    }

    /**
     * Add one cart line. Same product_id + same unit_name merges into existing row.
     */
    private function addToCartWithUnit(
        int $productId,
        string $name,
        string $sku,
        string $unitName,
        int $conversionFactor,
        int $unitPrice,
        int $quantity
    ): void {
        foreach ($this->cart as $key => $item) {
            if ($item['product_id'] === $productId && ($item['unit_name'] ?? '') === $unitName) {
                $this->cart[$key]['quantity'] += $quantity;
                $this->cart[$key]['subtotal'] = ($this->cart[$key]['unit_price'] * $this->cart[$key]['quantity']) - $this->cart[$key]['discount'];

                return;
            }
        }

        $this->cart[] = [
            'product_id' => $productId,
            'name' => $name,
            'sku' => $sku,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount' => 0,
            'subtotal' => $unitPrice * $quantity,
            'unit_name' => $unitName,
            'conversion_factor' => $conversionFactor,
        ];
    }

    public function updateQuantity(int $index, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeFromCart($index);

            return;
        }

        $this->cart[$index]['quantity'] = $quantity;
        $this->cart[$index]['subtotal'] = ($this->cart[$index]['unit_price'] * $quantity) - $this->cart[$index]['discount'];
    }

    /**
     * @param  int  $index  Cart index
     * @param  string  $value  Diskon dalam Rupiah (boleh koma desimal, e.g. 500,5)
     */
    public function updateItemDiscount(int $index, string $value): void
    {
        $discountCents = max(0, (int) round(parse_money_to_float($value) * 100));
        $this->cart[$index]['discount'] = $discountCents;
        $this->cart[$index]['subtotal'] = ($this->cart[$index]['unit_price'] * $this->cart[$index]['quantity']) - $this->cart[$index]['discount'];
    }

    public function removeFromCart(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->discountAmount = 0;
        $this->notes = '';
        $this->buyerName = '';
        $this->buyerPhone = '';
        $this->lastTransaction = null;
    }

    public function getSubtotalProperty(): int
    {
        return array_sum(array_column($this->cart, 'subtotal'));
    }

    public function getTotalProperty(): int
    {
        return max(0, $this->subtotal - $this->discountAmount);
    }

    public function getChangeProperty(): int
    {
        return max(0, $this->amountPaid - $this->total);
    }

    public function openPayment(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', type: 'error', message: 'Keranjang masih kosong.');

            return;
        }

        $this->amountPaid = $this->total;
        $this->showPaymentModal = true;
    }

    public function setAmountPaidFromRupiah(string $value): void
    {
        $this->amountPaid = max(0, (int) round(parse_money_to_float($value) * 100));
    }

    public function setDiscountAmountFromRupiah(string $value): void
    {
        $this->discountAmount = max(0, (int) round(parse_money_to_float($value) * 100));
    }

    public function processSale(): void
    {
        if (empty($this->cart)) {
            return;
        }

        if ($this->amountPaid < $this->total) {
            $this->addError('amountPaid', 'Jumlah bayar kurang dari total.');

            return;
        }

        try {
            $action = app(ProcessSale::class);

            $transaction = $action->execute(
                items: $this->cart,
                paymentMethod: PaymentMethod::from($this->paymentMethod),
                amountPaid: $this->amountPaid,
                discountAmount: $this->discountAmount,
                notes: $this->notes ?: null,
                buyerName: $this->buyerName ?: null,
                buyerPhone: $this->buyerPhone ?: null,
            );

            $this->lastTransaction = [
                'invoice_number' => $transaction->invoice_number,
                'completed_at' => $transaction->completed_at->format('d/m/Y H:i'),
                'buyer_name' => $transaction->buyer_name,
                'buyer_phone' => $transaction->buyer_phone,
                'items' => $transaction->items->map(fn ($item) => [
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_name' => $item->unit_name ?? 'pcs',
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount_amount,
                    'subtotal' => $item->subtotal,
                ])->values()->all(),
                'subtotal' => $transaction->subtotal,
                'discount_amount' => $transaction->discount_amount,
                'total_amount' => $transaction->total_amount,
                'payment_method' => $transaction->payment_method->label(),
                'amount_paid' => $transaction->amount_paid,
                'change_amount' => $transaction->change_amount,
            ];

            $this->cart = [];
            $this->discountAmount = 0;
            $this->notes = '';
            $this->buyerName = '';
            $this->buyerPhone = '';
            $this->showPaymentModal = false;

            $this->dispatch('notify', type: 'success', message: 'Transaksi berhasil! Invoice: '.$transaction->invoice_number);
            $this->dispatch('print-receipt');
        } catch (InsufficientStockException $e) {
            $this->addError('stock', $e->getMessage());
        } catch (\RuntimeException $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    public function render()
    {
        $receiptBranding = null;
        if ($this->lastTransaction) {
            $branding = app(BrandingService::class)->getBranding();
            $receiptBranding = [
                'name' => $branding['name'],
                'logo_url' => $branding['logo_path'] ? Storage::url($branding['logo_path']) : null,
                'primary_color' => $branding['primary_color'],
                'address' => $branding['address'] ?? null,
                'phone' => $branding['phone'] ?? null,
                'website' => $branding['website'] ?? null,
            ];
        }

        return view('livewire.pos.cashier', [
            'receiptBranding' => $receiptBranding,
        ])->layout('layouts.app', ['title' => __('Kasir / POS')]);
    }
}

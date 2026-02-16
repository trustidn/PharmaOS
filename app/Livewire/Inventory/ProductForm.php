<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;
use App\Services\PlanLimitService;
use Livewire\Component;

class ProductForm extends Component
{
    public ?Product $product = null;

    public string $sku = '';

    public ?string $barcode = null;

    public string $name = '';

    public ?string $generic_name = null;

    public ?string $description = null;

    public string $category_id = '';

    public string $unit_id = '';

    /** Base unit name (satuan terkecil), e.g. Butir, pcs */
    public string $base_unit = 'pcs';

    /** Harga jual dalam Rupiah (input dengan koma desimal, e.g. 2500,5) */
    public string $selling_price_rupiah = '0';

    public int $min_stock = 0;

    public bool $requires_prescription = false;

    /**
     * Additional sellable units: name, conversion_factor, price_sell_rupiah, barcode.
     * conversion_factor = how many base units per 1 of this unit (e.g. 1 Strip = 10 Butir).
     *
     * @var array<int, array{name: string, conversion_factor: string|int, price_sell_rupiah: string|int, barcode: string|null}>
     */
    public array $productUnits = [];

    public function mount(?int $productId = null): void
    {
        if ($productId) {
            $this->product = Product::with('productUnits')->findOrFail($productId);
            $this->fill($this->product->only([
                'sku', 'barcode', 'name', 'generic_name', 'description',
                'min_stock', 'requires_prescription',
            ]));
            $this->selling_price_rupiah = format_rupiah_input($this->product->selling_price);
            $this->base_unit = $this->product->getAttribute('base_unit') ?? 'pcs';
            $this->category_id = (string) ($this->product->category_id ?? '');
            $this->unit_id = (string) $this->product->unit_id;

            foreach ($this->product->productUnits as $pu) {
                $this->productUnits[] = [
                    'name' => $pu->name,
                    'conversion_factor' => (string) $pu->conversion_factor,
                    'price_sell_rupiah' => format_rupiah_input($pu->price_sell),
                    'barcode' => $pu->barcode ?? '',
                ];
            }
        }
    }

    public function addProductUnit(): void
    {
        $this->productUnits[] = [
            'name' => '',
            'conversion_factor' => '',
            'price_sell_rupiah' => '',
            'barcode' => '',
        ];
    }

    public function removeProductUnit(int $index): void
    {
        array_splice($this->productUnits, $index, 1);
        $this->productUnits = array_values($this->productUnits);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $uniqueRule = 'unique:products,sku';
        if ($this->product) {
            $uniqueRule .= ','.$this->product->id.',id,tenant_id,'.$this->product->tenant_id;
        }

        $rules = [
            'sku' => ['required', 'string', 'max:100', $uniqueRule],
            'name' => ['required', 'string', 'max:255'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'base_unit' => ['required', 'string', 'max:50'],
            'selling_price_rupiah' => ['required', 'string'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'requires_prescription' => ['boolean'],
            'productUnits' => ['array'],
            'productUnits.*.name' => ['required_with:productUnits.*.conversion_factor', 'nullable', 'string', 'max:100'],
            'productUnits.*.conversion_factor' => ['required_with:productUnits.*.name', 'nullable', 'integer', 'min:2'],
            'productUnits.*.price_sell_rupiah' => ['nullable', 'numeric', 'min:0'],
            'productUnits.*.barcode' => ['nullable', 'string', 'max:100'],
        ];

        return $rules;
    }

    public function save(): void
    {
        $this->validate();

        if (! $this->product) {
            $limitService = app(PlanLimitService::class);

            if (! $limitService->canAddProduct()) {
                $this->addError('limit', 'Batas jumlah produk untuk paket Anda telah tercapai.');

                return;
            }
        }

        $sellingPriceCents = (int) round(parse_money_to_float($this->selling_price_rupiah) * 100);
        if ($sellingPriceCents < 0) {
            $this->addError('selling_price_rupiah', 'Harga jual harus tidak negatif.');

            return;
        }

        $data = [
            'sku' => $this->sku,
            'barcode' => $this->barcode ?: null,
            'name' => $this->name,
            'generic_name' => $this->generic_name ?: null,
            'description' => $this->description ?: null,
            'category_id' => $this->category_id ?: null,
            'unit_id' => $this->unit_id,
            'base_unit' => $this->base_unit,
            'selling_price' => $sellingPriceCents,
            'min_stock' => $this->min_stock,
            'requires_prescription' => $this->requires_prescription,
        ];

        if ($this->product) {
            $this->product->update($data);
            $this->syncProductUnits($this->product);
            session()->flash('success', 'Produk berhasil diperbarui.');
        } else {
            $product = Product::create($data);
            $this->syncProductUnits($product);
            session()->flash('success', 'Produk berhasil ditambahkan.');
        }

        $this->redirect(route('inventory.products'), navigate: true);
    }

    private function syncProductUnits(Product $product): void
    {
        $product->productUnits()->delete();

        foreach ($this->productUnits as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $factor = (int) ($row['conversion_factor'] ?? 0);

            if ($name === '' || $factor < 2) {
                continue;
            }

            $priceRupiah = parse_money_to_float($row['price_sell_rupiah'] ?? '');
            $priceCents = (int) round($priceRupiah * 100);

            ProductUnit::create([
                'product_id' => $product->id,
                'name' => $name,
                'conversion_factor' => $factor,
                'price_sell' => $priceCents,
                'barcode' => trim((string) ($row['barcode'] ?? '')) ?: null,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.inventory.product-form', [
            'categories' => Category::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
        ]);
    }
}

<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ __('Buat Purchase Order') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Pilih supplier dan tambahkan item produk yang dibeli.') }}</flux:text>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Informasi PO') }}</flux:heading>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Supplier') }}</flux:label>
                    <flux:select wire:model="supplier_id" placeholder="{{ __('Pilih Supplier') }}" required>
                        @foreach ($suppliers as $s)
                            <flux:select.option :value="$s->id">{{ $s->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="supplier_id" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Tanggal Order') }}</flux:label>
                    <flux:input type="date" wire:model="ordered_at" required />
                    <flux:error name="ordered_at" />
                </flux:field>
                <flux:field class="sm:col-span-2">
                    <flux:label>{{ __('Catatan') }}</flux:label>
                    <flux:textarea wire:model="notes" rows="2" />
                </flux:field>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">{{ __('Item Pembelian') }}</flux:heading>
                <flux:button type="button" variant="ghost" icon="plus" wire:click="addItem">
                    {{ __('Tambah Item') }}
                </flux:button>
            </div>

            @if (count($items) > 0)
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Produk') }}</flux:table.column>
                        <flux:table.column>{{ __('Satuan pembelian') }}</flux:table.column>
                        <flux:table.column>{{ __('Qty') }}</flux:table.column>
                        <flux:table.column>{{ __('Harga Beli (Rp)') }}</flux:table.column>
                        <flux:table.column>{{ __('Subtotal') }}</flux:table.column>
                        <flux:table.column></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($items as $index => $item)
                            @php
                                $selectedProduct = $products->firstWhere('id', (int)($item['product_id'] ?? 0));
                            @endphp
                            <flux:table.row wire:key="item-{{ $index }}">
                                <flux:table.cell>
                                    <flux:select wire:model.live="items.{{ $index }}.product_id" placeholder="{{ __('Pilih Produk') }}">
                                        <flux:select.option value="">{{ __('-- Pilih Produk --') }}</flux:select.option>
                                        @foreach ($products as $p)
                                            <flux:select.option :value="$p->id">{{ $p->name }} ({{ $p->sku }})</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($selectedProduct)
                                        <flux:select wire:model.live="items.{{ $index }}.order_unit_key">
                                            <flux:select.option value="base">{{ $selectedProduct->base_unit ?? 'pcs' }} (satuan dasar)</flux:select.option>
                                            @foreach ($selectedProduct->productUnits ?? [] as $pu)
                                                <flux:select.option :value="$pu->id">{{ $pu->name }} (1 {{ $pu->name }} = {{ $pu->conversion_factor }} {{ $selectedProduct->base_unit ?? 'pcs' }})</flux:select.option>
                                            @endforeach
                                        </flux:select>
                                    @else
                                        <flux:badge size="sm" color="zinc">{{ $item['order_unit_name'] ?? 'pcs' }}</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:input type="number" wire:model.live="items.{{ $index }}.quantity" min="1" class="w-20" />
                                    <span class="text-xs text-zinc-500">({{ $item['order_unit_name'] ?? 'pcs' }})</span>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:input type="text" wire:model.live="items.{{ $index }}.unit_price" inputmode="decimal" placeholder="0 atau 2500,5" class="w-28" />
                                    <span class="text-xs text-zinc-500">/ {{ $item['order_unit_name'] ?? 'pcs' }}</span>
                                </flux:table.cell>
                                <flux:table.cell class="font-medium">Rp {{ number_format($item['subtotal'] ?? 0, 0, ',', '.') }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:button type="button" size="sm" variant="ghost" icon="trash" wire:click="removeItem({{ $index }})" />
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
                <div class="mt-4 text-right">
                    <flux:heading size="lg">{{ __('Total: Rp :total', ['total' => number_format($this->totalAmount, 0, ',', '.')]) }}</flux:heading>
                </div>
            @endif
        </flux:card>

        <div class="flex items-center gap-3">
            <flux:button type="submit" variant="primary" :disabled="count($items) < 1">
                {{ __('Simpan PO') }}
            </flux:button>
            <flux:button type="button" variant="ghost" :href="route('purchase-orders.index')" wire:navigate>
                {{ __('Batal') }}
            </flux:button>
        </div>
    </form>
</div>

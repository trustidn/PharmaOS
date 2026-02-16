<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ __('Terima Barang - :po', ['po' => $order->invoice_number]) }}</flux:heading>
        <flux:text class="mt-1">{{ __('Supplier: :name', ['name' => $order->supplier->name]) }}</flux:text>
    </div>

    @if (session('error'))
        <flux:callout variant="danger" icon="exclamation-triangle" class="mb-4">
            {{ session('error') }}
        </flux:callout>
    @endif

    <form wire:submit="receive" class="space-y-6">
        <flux:card>
            <flux:field>
                <flux:label>{{ __('Tanggal Diterima') }}</flux:label>
                <flux:input type="date" wire:model="received_at" required />
                <flux:error name="received_at" />
            </flux:field>
        </flux:card>

        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Detail Item - Isi No. Batch & Kadaluarsa') }}</flux:heading>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Produk') }}</flux:table.column>
                    <flux:table.column>{{ __('Qty pesanan') }}</flux:table.column>
                    <flux:table.column>{{ __('No. Batch') }}</flux:table.column>
                    <flux:table.column>{{ __('Kadaluarsa') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($order->items as $item)
                        <flux:table.row wire:key="receive-{{ $item->id }}">
                            <flux:table.cell>
                                <div class="font-medium">{{ $item->product->name }}</div>
                                <div class="text-xs text-zinc-500">{{ $item->product->sku }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ number_format($item->quantity) }} {{ $item->order_unit_name ?? 'pcs' }}
                                <span class="text-xs text-zinc-500">→ {{ number_format($item->quantityInBaseUnit()) }} {{ $item->product->base_unit ?? 'pcs' }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:input wire:model="itemData.{{ $item->id }}.batch_number" placeholder="BATCH-XXXXXX" required />
                                <flux:error name="itemData.{{ $item->id }}.batch_number" />
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:input type="date" wire:model="itemData.{{ $item->id }}.expired_at" required />
                                <flux:error name="itemData.{{ $item->id }}.expired_at" />
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>

        <div class="flex items-center gap-3">
            <flux:button type="submit" variant="primary">
                {{ __('Konfirmasi Terima Barang') }}
            </flux:button>
            <flux:button type="button" variant="ghost" :href="route('purchase-orders.index')" wire:navigate>
                {{ __('Batal') }}
            </flux:button>
        </div>
    </form>
</div>

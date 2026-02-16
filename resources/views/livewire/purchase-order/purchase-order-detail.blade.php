<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Detail PO - :invoice', ['invoice' => $order->invoice_number]) }}</flux:heading>
            <flux:text class="mt-1">{{ __('Supplier: :name', ['name' => $order->supplier->name]) }}</flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button variant="ghost" icon="arrow-left" :href="route('purchase-orders.index')" wire:navigate>
                {{ __('Kembali') }}
            </flux:button>
            @if (!$order->isReceived())
                <flux:button variant="primary" icon="truck" :href="route('purchase-orders.receive', $order)" wire:navigate>
                    {{ __('Terima Barang') }}
                </flux:button>
            @endif
        </div>
    </div>

    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">{{ __('Informasi PO') }}</flux:heading>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <flux:text size="sm" class="text-zinc-500">{{ __('No. PO') }}</flux:text>
                <div class="font-mono font-medium">{{ $order->invoice_number }}</div>
            </div>
            <div>
                <flux:text size="sm" class="text-zinc-500">{{ __('Supplier') }}</flux:text>
                <div class="font-medium">{{ $order->supplier->name }}</div>
            </div>
            <div>
                <flux:text size="sm" class="text-zinc-500">{{ __('Tanggal Order') }}</flux:text>
                <div>{{ $order->ordered_at->format('d M Y') }}</div>
            </div>
            <div>
                <flux:text size="sm" class="text-zinc-500">{{ __('Tanggal Diterima') }}</flux:text>
                <div>{{ $order->received_at?->format('d M Y') ?? __('Belum diterima') }}</div>
            </div>
            <div>
                <flux:text size="sm" class="text-zinc-500">{{ __('Dibuat oleh') }}</flux:text>
                <div>{{ $order->creator->name }}</div>
            </div>
            <div>
                <flux:text size="sm" class="text-zinc-500">{{ __('Status') }}</flux:text>
                <div>
                    @if ($order->isReceived())
                        <flux:badge color="green" size="sm">{{ __('Diterima') }}</flux:badge>
                    @else
                        <flux:badge color="amber" size="sm">{{ __('Belum Diterima') }}</flux:badge>
                    @endif
                </div>
            </div>
        </div>
        @if ($order->notes)
            <div class="mt-4">
                <flux:text size="sm" class="text-zinc-500">{{ __('Catatan') }}</flux:text>
                <div class="mt-1">{{ $order->notes }}</div>
            </div>
        @endif
        <div class="mt-4 border-t border-zinc-200 pt-4 dark:border-zinc-700">
            <flux:text size="sm" class="text-zinc-500">{{ __('Total Pembelian') }}</flux:text>
            <div class="text-2xl font-bold">Rp {{ number_format($order->total_amount / 100, 0, ',', '.') }}</div>
        </div>
    </flux:card>

    <flux:card>
        <flux:heading size="lg" class="mb-4">{{ __('Item Pembelian') }}</flux:heading>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Produk') }}</flux:table.column>
                <flux:table.column>{{ __('Qty') }}</flux:table.column>
                <flux:table.column>{{ __('Harga Beli') }}</flux:table.column>
                <flux:table.column>{{ __('Subtotal') }}</flux:table.column>
                @if ($order->isReceived())
                    <flux:table.column>{{ __('Batch') }}</flux:table.column>
                    <flux:table.column>{{ __('Kadaluarsa') }}</flux:table.column>
                @endif
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($order->items as $item)
                    <flux:table.row wire:key="item-{{ $item->id }}">
                        <flux:table.cell>
                            <div class="font-medium">{{ $item->product->name }}</div>
                            <div class="text-xs text-zinc-500">{{ $item->product->sku }} · {{ $item->product->unit->abbreviation ?? '-' }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ number_format($item->quantity) }} {{ $item->order_unit_name ?? 'pcs' }}
                            @if (($item->conversion_factor ?? 1) > 1)
                                <span class="text-xs text-zinc-500">(= {{ number_format($item->quantityInBaseUnit()) }} {{ $item->product->base_unit ?? 'pcs' }})</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>Rp {{ number_format($item->unit_price / 100, 0, ',', '.') }}/{{ $item->order_unit_name ?? 'pcs' }}</flux:table.cell>
                        <flux:table.cell class="font-semibold">Rp {{ number_format($item->subtotal / 100, 0, ',', '.') }}</flux:table.cell>
                        @if ($order->isReceived())
                            <flux:table.cell>
                                @if ($item->batch)
                                    <flux:badge size="sm" color="zinc">{{ $item->batch->batch_number }}</flux:badge>
                                @else
                                    —
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($item->batch)
                                    {{ $item->batch->expired_at->format('d M Y') }}
                                @else
                                    —
                                @endif
                            </flux:table.cell>
                        @endif
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>

<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Batch - :product', ['product' => $product->name]) }}</flux:heading>
            <flux:text class="mt-1">{{ __('Kelola batch dan stok untuk produk ini. SKU: :sku', ['sku' => $product->sku]) }}</flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button variant="ghost" icon="arrow-left" :href="route('inventory.products')" wire:navigate>
                {{ __('Kembali') }}
            </flux:button>
            <flux:button variant="primary" icon="plus" :href="route('inventory.batches.create', $product)" wire:navigate>
                {{ __('Tambah Batch') }}
            </flux:button>
        </div>
    </div>

    @if (session('success'))
        <flux:callout variant="success" icon="check-circle" class="mb-4">
            {{ session('success') }}
        </flux:callout>
    @endif

    <div class="mb-4 flex flex-col gap-3 sm:flex-row">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Cari nomor batch...') }}" icon="magnifying-glass" />
        </div>
        <flux:select wire:model.live="statusFilter" placeholder="{{ __('Semua Status') }}" class="w-full sm:w-48">
            <flux:select.option value="">{{ __('Semua Status') }}</flux:select.option>
            <flux:select.option value="active">{{ __('Aktif') }}</flux:select.option>
            <flux:select.option value="near_expiry">{{ __('Mendekati Kadaluarsa') }}</flux:select.option>
            <flux:select.option value="expired">{{ __('Kadaluarsa') }}</flux:select.option>
        </flux:select>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('No. Batch') }}</flux:table.column>
            <flux:table.column>{{ __('Harga Beli') }}</flux:table.column>
            <flux:table.column>{{ __('Qty Diterima') }}</flux:table.column>
            <flux:table.column>{{ __('Sisa Stok') }}</flux:table.column>
            <flux:table.column>{{ __('Tgl Diterima') }}</flux:table.column>
            <flux:table.column>{{ __('Kadaluarsa') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column>{{ __('Aksi') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($batches as $batch)
                <flux:table.row wire:key="batch-{{ $batch->id }}">
                    <flux:table.cell>
                        <flux:badge size="sm" color="zinc">{{ $batch->batch_number }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $batch->formatMoney($batch->purchase_price) }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($batch->purchaseOrderItem)
                            @php
                                $poItem = $batch->purchaseOrderItem;
                                $factor = max(1, $poItem->conversion_factor);
                                $qtyOrderUnit = (int) ($batch->quantity_received / $factor);
                            @endphp
                            {{ number_format($qtyOrderUnit) }} {{ $poItem->order_unit_name }}
                            @if ($factor > 1)
                                <span class="text-zinc-500 text-xs">({{ number_format($batch->quantity_received) }} {{ $product->base_unit ?? 'pcs' }})</span>
                            @endif
                        @else
                            {{ number_format($batch->quantity_received) }} {{ $product->base_unit ?? 'pcs' }}
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <span @class(['font-semibold', 'text-red-600 dark:text-red-400' => $batch->quantity_remaining === 0])>
                            {{ number_format($batch->quantity_remaining) }}
                        </span>
                    </flux:table.cell>
                    <flux:table.cell>{{ $batch->received_at->format('d M Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <span @class([
                            'text-red-600 dark:text-red-400 font-semibold' => $batch->isExpired(),
                            'text-amber-600 dark:text-amber-400' => $batch->isNearExpiry() && !$batch->isExpired(),
                        ])>
                            {{ $batch->expired_at->format('d M Y') }}
                        </span>
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($batch->isExpired())
                            <flux:badge color="red" size="sm">{{ __('Kadaluarsa') }}</flux:badge>
                        @elseif (!$batch->is_active)
                            <flux:badge color="zinc" size="sm">{{ __('Nonaktif') }}</flux:badge>
                        @elseif ($batch->isNearExpiry())
                            <flux:badge color="amber" size="sm">{{ __('Hampir ED') }}</flux:badge>
                        @else
                            <flux:badge color="green" size="sm">{{ __('Aktif') }}</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-1">
                            <flux:button size="sm" variant="ghost" icon="pencil-square" :href="route('inventory.batches.edit', [$product, $batch])" wire:navigate />
                            @if ($batch->is_active && !$batch->isExpired())
                                <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="deactivateBatch({{ $batch->id }})" wire:confirm="{{ __('Yakin ingin menonaktifkan batch ini?') }}" />
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center py-8">
                        <flux:text>{{ __('Belum ada batch untuk produk ini.') }}</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $batches->links() }}
    </div>
</div>

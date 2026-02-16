<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ __('Laporan Stok') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Ringkasan stok produk apotek.') }}</flux:text>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <flux:card>
            <flux:text size="sm">{{ __('Total Produk') }}</flux:text>
            <div class="mt-1 text-2xl font-bold">{{ number_format($totalProducts) }}</div>
        </flux:card>
        <flux:card>
            <flux:text size="sm">{{ __('Stok Rendah') }}</flux:text>
            <div class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($lowStockCount) }}</div>
        </flux:card>
        <flux:card>
            <flux:text size="sm">{{ __('Perlu Perhatian') }}</flux:text>
            <div class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($lowStockCount) }} {{ __('item') }}</div>
        </flux:card>
    </div>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Cari produk...') }}" icon="magnifying-glass" />
        </div>
        <flux:select wire:model.live="filter" class="w-full sm:w-40">
            <flux:select.option value="">{{ __('Semua') }}</flux:select.option>
            <flux:select.option value="low">{{ __('Stok Rendah') }}</flux:select.option>
            <flux:select.option value="empty">{{ __('Stok Habis') }}</flux:select.option>
        </flux:select>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('SKU') }}</flux:table.column>
            <flux:table.column>{{ __('Nama Produk') }}</flux:table.column>
            <flux:table.column>{{ __('Kategori') }}</flux:table.column>
            <flux:table.column>{{ __('Satuan') }}</flux:table.column>
            <flux:table.column>{{ __('Stok Tersedia') }}</flux:table.column>
            <flux:table.column>{{ __('Min Stok') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($products as $product)
                @php $stock = $product->total_stock ?? 0; @endphp
                <flux:table.row wire:key="stock-{{ $product->id }}">
                    <flux:table.cell><flux:badge size="sm" color="zinc">{{ $product->sku }}</flux:badge></flux:table.cell>
                    <flux:table.cell class="font-medium">{{ $product->name }}</flux:table.cell>
                    <flux:table.cell>{{ $product->category?->name ?? '-' }}</flux:table.cell>
                    <flux:table.cell>{{ $product->unit->abbreviation }}</flux:table.cell>
                    <flux:table.cell class="font-semibold">{{ number_format($stock) }}</flux:table.cell>
                    <flux:table.cell>{{ number_format($product->min_stock) }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($stock <= 0)
                            <flux:badge color="red" size="sm">{{ __('Habis') }}</flux:badge>
                        @elseif ($stock <= $product->min_stock)
                            <flux:badge color="amber" size="sm">{{ __('Rendah') }}</flux:badge>
                        @else
                            <flux:badge color="green" size="sm">{{ __('Aman') }}</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:button size="sm" variant="ghost" icon="eye" wire:click="openStockDetail({{ $product->id }})">
                            {{ __('Detail') }}
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center py-8">
                        <flux:text>{{ __('Tidak ada data produk.') }}</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">{{ $products->links() }}</div>

    {{-- Modal detail stok per batch --}}
    <flux:modal wire:model="showStockDetailModal" class="max-w-2xl">
        @if ($detailProduct)
            <flux:heading size="lg">{{ __('Detail Stok') }}: {{ $detailProduct->name }}</flux:heading>
            <flux:text class="mt-1">{{ $detailProduct->sku }} · {{ __('Satuan') }}: {{ $detailProduct->unit?->abbreviation ?? $detailProduct->base_unit ?? 'pcs' }}</flux:text>

            <div class="mt-4 overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('No. Batch') }}</flux:table.column>
                        <flux:table.column>{{ __('Qty Tersedia') }}</flux:table.column>
                        <flux:table.column>{{ __('Kadaluarsa') }}</flux:table.column>
                        <flux:table.column>{{ __('Tanggal Terima') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($detailBatches as $batch)
                            <flux:table.row wire:key="batch-{{ $batch->id }}">
                                <flux:table.cell><flux:badge size="sm" color="zinc">{{ $batch->batch_number }}</flux:badge></flux:table.cell>
                                <flux:table.cell class="font-medium">{{ number_format($batch->quantity_remaining) }}</flux:table.cell>
                                <flux:table.cell>{{ $batch->expired_at?->format('d M Y') }}</flux:table.cell>
                                <flux:table.cell>{{ $batch->received_at?->format('d M Y') }}</flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="4" class="text-center py-4 text-zinc-500">
                                    {{ __('Tidak ada batch dengan stok tersedia.') }}
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>

            @if ($detailBatches->isNotEmpty())
                <div class="mt-3 rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-800">
                    {{ __('Total stok tersedia') }}: <strong>{{ number_format($detailBatches->sum('quantity_remaining')) }}</strong> {{ $detailProduct->unit?->abbreviation ?? $detailProduct->base_unit ?? 'pcs' }}
                </div>
            @endif
        @endif

        <div class="mt-4 flex justify-end">
            <flux:button variant="ghost" wire:click="closeStockDetail">{{ __('Tutup') }}</flux:button>
        </div>
    </flux:modal>
</div>

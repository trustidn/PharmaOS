<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Produk / Obat') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Kelola master data produk dan obat apotek Anda.') }}</flux:text>
        </div>
        <flux:button variant="primary" icon="plus" :href="route('inventory.products.create')" wire:navigate>
            {{ __('Tambah Produk') }}
        </flux:button>
    </div>

    @if (session('success'))
        <flux:callout variant="success" icon="check-circle" class="mb-4">
            {{ session('success') }}
        </flux:callout>
    @endif

    <div class="mb-4 flex flex-col gap-3 sm:flex-row">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Cari nama, SKU, barcode...') }}" icon="magnifying-glass" />
        </div>
        <flux:select wire:model.live="categoryFilter" placeholder="{{ __('Semua Kategori') }}" class="w-full sm:w-48">
            <flux:select.option value="">{{ __('Semua Kategori') }}</flux:select.option>
            @foreach ($categories as $category)
                <flux:select.option :value="$category->id">{{ $category->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="stockFilter" placeholder="{{ __('Semua Stok') }}" class="w-full sm:w-40">
            <flux:select.option value="">{{ __('Semua Stok') }}</flux:select.option>
            <flux:select.option value="low">{{ __('Stok Rendah') }}</flux:select.option>
        </flux:select>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('SKU') }}</flux:table.column>
            <flux:table.column>{{ __('Nama Produk') }}</flux:table.column>
            <flux:table.column>{{ __('Kategori') }}</flux:table.column>
            <flux:table.column>{{ __('Satuan') }}</flux:table.column>
            <flux:table.column>{{ __('Harga Jual') }}</flux:table.column>
            <flux:table.column>{{ __('Stok') }}</flux:table.column>
            <flux:table.column>{{ __('Aksi') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($products as $product)
                <flux:table.row wire:key="product-{{ $product->id }}">
                    <flux:table.cell>
                        <flux:badge size="sm" color="zinc">{{ $product->sku }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div>
                            <span class="font-medium">{{ $product->name }}</span>
                            @if ($product->requires_prescription)
                                <flux:badge size="sm" color="red" class="ml-1">Resep</flux:badge>
                            @endif
                            @if ($product->generic_name)
                                <div class="text-xs text-zinc-500">{{ $product->generic_name }}</div>
                            @endif
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>{{ $product->category?->name ?? '-' }}</flux:table.cell>
                    <flux:table.cell>{{ $product->unit->abbreviation }}</flux:table.cell>
                    <flux:table.cell>{{ $product->formattedSellingPrice() }}</flux:table.cell>
                    <flux:table.cell>
                        @php $stock = $product->totalStock(); @endphp
                        <span @class(['text-red-600 dark:text-red-400 font-semibold' => $stock <= $product->min_stock])>
                            {{ number_format($stock) }}
                        </span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-1">
                            <flux:button size="sm" variant="ghost" icon="eye" :href="route('inventory.batches', $product)" wire:navigate />
                            <flux:button size="sm" variant="ghost" icon="pencil-square" :href="route('inventory.products.edit', $product)" wire:navigate />
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteProduct({{ $product->id }})" wire:confirm="{{ __('Yakin ingin menonaktifkan produk ini?') }}" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center py-8">
                        <flux:text>{{ __('Belum ada produk. Tambahkan produk pertama Anda.') }}</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
</div>

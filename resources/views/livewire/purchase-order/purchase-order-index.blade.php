<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Purchase Order') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Buat dan kelola pembelian dari supplier. Terima barang untuk menambah stok.') }}</flux:text>
        </div>
        <flux:button variant="primary" icon="plus" :href="route('purchase-orders.create')" wire:navigate>
            {{ __('Buat PO') }}
        </flux:button>
    </div>

    @if (session('success'))
        <flux:callout variant="success" icon="check-circle" class="mb-4">
            {{ session('success') }}
        </flux:callout>
    @endif
    @if (session('error'))
        <flux:callout variant="danger" icon="exclamation-triangle" class="mb-4">
            {{ session('error') }}
        </flux:callout>
    @endif

    <div class="mb-4 flex flex-col gap-3 sm:flex-row">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Cari nomor PO...') }}" icon="magnifying-glass" />
        </div>
        <flux:select wire:model.live="period" class="w-full sm:w-40">
            <flux:select.option value="all">{{ __('Semua Periode') }}</flux:select.option>
            <flux:select.option value="today">{{ __('Hari Ini') }}</flux:select.option>
            <flux:select.option value="month">{{ __('Bulan Ini') }}</flux:select.option>
            <flux:select.option value="year">{{ __('Tahun Ini') }}</flux:select.option>
            <flux:select.option value="custom">{{ __('Kustom') }}</flux:select.option>
        </flux:select>
        @if ($period === 'custom')
            <flux:input type="date" wire:model.live="dateFrom" placeholder="{{ __('Dari') }}" />
            <flux:input type="date" wire:model.live="dateTo" placeholder="{{ __('Sampai') }}" />
        @endif
        <flux:select wire:model.live="supplierFilter" placeholder="{{ __('Semua Supplier') }}" class="w-full sm:w-48">
            <flux:select.option value="">{{ __('Semua Supplier') }}</flux:select.option>
            @foreach ($suppliers as $s)
                <flux:select.option :value="$s->id">{{ $s->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="statusFilter" placeholder="{{ __('Semua Status') }}" class="w-full sm:w-40">
            <flux:select.option value="">{{ __('Semua') }}</flux:select.option>
            <flux:select.option value="pending">{{ __('Belum Diterima') }}</flux:select.option>
            <flux:select.option value="received">{{ __('Sudah Diterima') }}</flux:select.option>
        </flux:select>
    </div>

    <div class="mb-4 rounded-lg bg-zinc-50 px-4 py-3 dark:bg-zinc-800">
        <flux:text size="sm">{{ ($period !== 'all' || $search || $supplierFilter || $statusFilter) ? __('Total Pembelian (sesuai filter)') : __('Total Pembelian') }}</flux:text>
        <div class="text-xl font-bold">Rp {{ number_format($totalAmount / 100, 0, ',', '.') }}</div>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('No. PO') }}</flux:table.column>
            <flux:table.column>{{ __('Supplier') }}</flux:table.column>
            <flux:table.column>{{ __('Tanggal Order') }}</flux:table.column>
            <flux:table.column>{{ __('Total') }}</flux:table.column>
            <flux:table.column>{{ __('Dibuat oleh') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column>{{ __('Aksi') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($orders as $order)
                <flux:table.row wire:key="po-{{ $order->id }}">
                    <flux:table.cell class="font-mono text-sm">{{ $order->invoice_number }}</flux:table.cell>
                    <flux:table.cell>{{ $order->supplier->name }}</flux:table.cell>
                    <flux:table.cell>{{ $order->ordered_at->format('d M Y') }}</flux:table.cell>
                    <flux:table.cell class="font-semibold">Rp {{ number_format($order->total_amount / 100, 0, ',', '.') }}</flux:table.cell>
                    <flux:table.cell>{{ $order->creator->name }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($order->isReceived())
                            <flux:badge color="green" size="sm">{{ __('Diterima') }}</flux:badge>
                        @else
                            <flux:badge color="amber" size="sm">{{ __('Belum Diterima') }}</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-1">
                            <flux:button size="sm" variant="ghost" icon="eye" :href="route('purchase-orders.show', $order)" wire:navigate>
                                {{ __('Lihat') }}
                            </flux:button>
                            @if (!$order->isReceived())
                                <flux:button size="sm" variant="ghost" icon="truck" :href="route('purchase-orders.receive', $order)" wire:navigate>
                                    {{ __('Terima') }}
                                </flux:button>
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteOrder({{ $order->id }})" wire:confirm="{{ __('Yakin ingin membatalkan dan menghapus PO ini?') }}">
                                    {{ __('Hapus') }}
                                </flux:button>
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center py-8">
                        <flux:text>{{ __('Belum ada Purchase Order. Klik "Buat PO" untuk membuat pembelian dari supplier.') }}</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">{{ $orders->links() }}</div>
</div>

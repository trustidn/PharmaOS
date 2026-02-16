<div>
    {{-- Salam & info user --}}
    <div class="mb-6">
        <flux:heading size="xl">
            {{ $data['greeting'] }}, {{ $data['userName'] }}!
        </flux:heading>
        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">{{ $data['userRoleLabel'] }}</flux:text>
    </div>

    @if ($isTenant && isset($data['subscription']) && $data['subscription'])
        {{-- Info paket langganan --}}
        <flux:card class="mb-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-teal-100 p-2 dark:bg-teal-900/30">
                        <flux:icon name="credit-card" class="h-6 w-6 text-teal-600 dark:text-teal-400" />
                    </div>
                    <div>
                        <flux:heading size="lg">{{ __('Paket Langganan') }}</flux:heading>
                        <flux:text class="mt-0.5">
                            {{ $data['subscription']->plan->label() }}
                            <flux:badge color="zinc" size="sm" class="ms-2">{{ $data['subscription']->status->label() }}</flux:badge>
                            @if ($data['subscription']->trial_ends_at && $data['subscription']->status->value === 'trial')
                                <span class="ml-2 text-sm text-zinc-500 dark:text-zinc-400">
                                    ({{ __('Trial hingga') }} {{ $data['subscription']->trial_ends_at->translatedFormat('d F Y') }})
                                </span>
                            @endif
                        </flux:text>
                    </div>
                </div>
                <div class="flex flex-wrap gap-6 text-sm text-zinc-600 dark:text-zinc-400">
                    <span>{{ __('Maks. produk') }}: {{ $data['subscription']->max_products === PHP_INT_MAX ? '∞' : number_format($data['subscription']->max_products) }}</span>
                    <span>{{ __('Maks. user') }}: {{ $data['subscription']->max_users === PHP_INT_MAX ? '∞' : number_format($data['subscription']->max_users) }}</span>
                    <span>{{ __('Transaksi/bulan') }}: {{ $data['subscription']->max_transactions_per_month === PHP_INT_MAX ? '∞' : number_format($data['subscription']->max_transactions_per_month) }}</span>
                </div>
            </div>
        </flux:card>
    @endif

    @if ($isTenant)
        {{-- Summary Cards --}}
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <flux:card>
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30">
                        <flux:icon name="banknotes" class="h-6 w-6 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <flux:text size="sm">{{ __('Pendapatan Hari Ini') }}</flux:text>
                        <div class="text-xl font-bold text-green-600 dark:text-green-400">Rp {{ number_format($data['todayRevenue'] / 100, 0, ',', '.') }}</div>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                        <flux:icon name="shopping-cart" class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <flux:text size="sm">{{ __('Transaksi Hari Ini') }}</flux:text>
                        <div class="text-xl font-bold">{{ number_format($data['todayTransactions']) }}</div>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/30">
                        <flux:icon name="chart-bar" class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                        <flux:text size="sm">{{ __('Pendapatan Bulan Ini') }}</flux:text>
                        <div class="text-xl font-bold">Rp {{ number_format($data['monthRevenue'] / 100, 0, ',', '.') }}</div>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-zinc-100 p-2 dark:bg-zinc-800">
                        <flux:icon name="cube" class="h-6 w-6 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <div>
                        <flux:text size="sm">{{ __('Total Produk Aktif') }}</flux:text>
                        <div class="text-xl font-bold">{{ number_format($data['totalProducts']) }}</div>
                    </div>
                </div>
            </flux:card>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Low Stock Alert --}}
            <flux:card>
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="lg">{{ __('Stok Rendah') }}</flux:heading>
                    @if (auth()->user()->isOwner())
                        <flux:button size="sm" variant="ghost" :href="route('reports.stock')" wire:navigate>{{ __('Lihat Semua') }}</flux:button>
                    @endif
                </div>
                @forelse ($data['lowStockProducts'] as $product)
                    <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-700 last:border-0">
                        <div>
                            <div class="font-medium text-sm">{{ $product->name }}</div>
                            <div class="text-xs text-zinc-500">{{ $product->sku }}</div>
                        </div>
                        <flux:badge color="red" size="sm">{{ $product->available_stock ?? 0 }} / {{ $product->min_stock }}</flux:badge>
                    </div>
                @empty
                    <flux:text class="py-4 text-center">{{ __('Semua stok dalam kondisi aman.') }}</flux:text>
                @endforelse
            </flux:card>

            {{-- Near Expiry Alert --}}
            <flux:card>
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="lg">{{ __('Mendekati Kadaluarsa') }}</flux:heading>
                    @if (auth()->user()->isOwner())
                        <flux:button size="sm" variant="ghost" :href="route('reports.expiry')" wire:navigate>{{ __('Lihat Semua') }}</flux:button>
                    @endif
                </div>
                @forelse ($data['nearExpiryBatches'] as $batch)
                    <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-700 last:border-0">
                        <div>
                            <div class="font-medium text-sm">{{ $batch->product->name }}</div>
                            <div class="text-xs text-zinc-500">Batch: {{ $batch->batch_number }}</div>
                        </div>
                        <flux:badge color="amber" size="sm">{{ $batch->expired_at->format('d M Y') }}</flux:badge>
                    </div>
                @empty
                    <flux:text class="py-4 text-center">{{ __('Tidak ada obat mendekati kadaluarsa.') }}</flux:text>
                @endforelse
            </flux:card>

            {{-- Recent Transactions --}}
            <flux:card class="lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="lg">{{ __('Transaksi Terbaru') }}</flux:heading>
                    <flux:button size="sm" variant="ghost" :href="route('pos.transactions')" wire:navigate>{{ __('Lihat Semua') }}</flux:button>
                </div>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Invoice') }}</flux:table.column>
                        <flux:table.column>{{ __('Waktu') }}</flux:table.column>
                        <flux:table.column>{{ __('Kasir') }}</flux:table.column>
                        <flux:table.column>{{ __('Total') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($data['recentTransactions'] as $tx)
                            <flux:table.row wire:key="recent-{{ $tx->id }}">
                                <flux:table.cell class="font-mono text-sm">{{ $tx->invoice_number }}</flux:table.cell>
                                <flux:table.cell>{{ $tx->created_at->diffForHumans() }}</flux:table.cell>
                                <flux:table.cell>{{ $tx->cashier->name }}</flux:table.cell>
                                <flux:table.cell class="font-semibold">Rp {{ number_format($tx->total_amount / 100, 0, ',', '.') }}</flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="4" class="text-center py-4">
                                    <flux:text>{{ __('Belum ada transaksi.') }}</flux:text>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </div>
    @else
        {{-- Super Admin View --}}
        <div class="text-center py-12">
            <flux:icon name="building-office" class="mx-auto h-16 w-16 text-zinc-300" />
            <flux:heading size="xl" class="mt-4">{{ __('PharmaOS Admin') }}</flux:heading>
            <flux:text class="mt-2">{{ __('Gunakan menu Admin di sidebar untuk mengelola tenant.') }}</flux:text>
        </div>
    @endif
</div>

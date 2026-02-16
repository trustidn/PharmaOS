<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ __('Laporan Kadaluarsa') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Pantau batch obat yang mendekati atau sudah melewati tanggal kadaluarsa.') }}</flux:text>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <flux:card class="border-red-200 dark:border-red-800">
            <flux:text size="sm" class="text-red-600 dark:text-red-400">{{ __('Sudah Kadaluarsa') }}</flux:text>
            <div class="mt-1 text-3xl font-bold text-red-600 dark:text-red-400">{{ $expiredCount }}</div>
            <flux:text size="sm">{{ __('batch perlu ditarik') }}</flux:text>
        </flux:card>
        <flux:card class="border-amber-200 dark:border-amber-800">
            <flux:text size="sm" class="text-amber-600 dark:text-amber-400">{{ __('Mendekati Kadaluarsa') }}</flux:text>
            <div class="mt-1 text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $nearExpiryCount }}</div>
            <flux:text size="sm">{{ __('batch dalam :days hari', ['days' => $daysThreshold]) }}</flux:text>
        </flux:card>
    </div>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Cari produk...') }}" icon="magnifying-glass" />
        </div>
        <flux:select wire:model.live="filter" class="w-full sm:w-48">
            <flux:select.option value="near_expiry">{{ __('Mendekati Kadaluarsa') }}</flux:select.option>
            <flux:select.option value="expired">{{ __('Sudah Kadaluarsa') }}</flux:select.option>
        </flux:select>
        <flux:select wire:model.live="daysThreshold" class="w-full sm:w-36">
            <flux:select.option value="30">30 {{ __('hari') }}</flux:select.option>
            <flux:select.option value="60">60 {{ __('hari') }}</flux:select.option>
            <flux:select.option value="90">90 {{ __('hari') }}</flux:select.option>
            <flux:select.option value="180">180 {{ __('hari') }}</flux:select.option>
        </flux:select>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Produk') }}</flux:table.column>
            <flux:table.column>{{ __('No. Batch') }}</flux:table.column>
            <flux:table.column>{{ __('Sisa Stok') }}</flux:table.column>
            <flux:table.column>{{ __('Kadaluarsa') }}</flux:table.column>
            <flux:table.column>{{ __('Sisa Hari') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($batches as $batch)
                <flux:table.row wire:key="expiry-{{ $batch->id }}">
                    <flux:table.cell>
                        <div class="font-medium">{{ $batch->product->name }}</div>
                        <div class="text-xs text-zinc-500">{{ $batch->product->sku }}</div>
                    </flux:table.cell>
                    <flux:table.cell><flux:badge size="sm" color="zinc">{{ $batch->batch_number }}</flux:badge></flux:table.cell>
                    <flux:table.cell>{{ number_format($batch->quantity_remaining) }} {{ $batch->product->unit->abbreviation }}</flux:table.cell>
                    <flux:table.cell>{{ $batch->expired_at->format('d M Y') }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($batch->isExpired())
                            <span class="font-semibold text-red-600 dark:text-red-400">{{ __('Lewat :days hari', ['days' => abs($batch->expired_at->diffInDays(now()))]) }}</span>
                        @else
                            <span @class([
                                'font-semibold',
                                'text-red-600 dark:text-red-400' => $batch->expired_at->diffInDays(now()) <= 30,
                                'text-amber-600 dark:text-amber-400' => $batch->expired_at->diffInDays(now()) > 30,
                            ])>
                                {{ $batch->expired_at->diffInDays(now()) }} {{ __('hari') }}
                            </span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($batch->isExpired())
                            <flux:badge color="red" size="sm">{{ __('Kadaluarsa') }}</flux:badge>
                        @else
                            <flux:badge color="amber" size="sm">{{ __('Hampir ED') }}</flux:badge>
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-8">
                        <flux:text>{{ __('Tidak ada batch yang mendekati kadaluarsa.') }}</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">{{ $batches->links() }}</div>
</div>

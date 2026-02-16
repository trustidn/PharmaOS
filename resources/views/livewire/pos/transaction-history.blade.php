<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ __('Riwayat Transaksi') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Lihat dan kelola riwayat transaksi penjualan.') }}</flux:text>
    </div>

    @if (session('success'))
        <flux:callout variant="success" icon="check-circle" class="mb-4">
            {{ session('success') }}
        </flux:callout>
    @endif

    <div class="mb-4 flex flex-col gap-3 sm:flex-row">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Cari nomor invoice...') }}" icon="magnifying-glass" />
        </div>
        <flux:select wire:model.live="statusFilter" placeholder="{{ __('Semua Status') }}" class="w-full sm:w-40">
            <flux:select.option value="">{{ __('Semua Status') }}</flux:select.option>
            @foreach ($statuses as $status)
                <flux:select.option :value="$status->value">{{ $status->label() }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:input type="date" wire:model.live="dateFrom" class="w-full sm:w-40" />
        <flux:input type="date" wire:model.live="dateTo" class="w-full sm:w-40" />
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Invoice') }}</flux:table.column>
            <flux:table.column>{{ __('Tanggal') }}</flux:table.column>
            <flux:table.column>{{ __('Kasir') }}</flux:table.column>
            <flux:table.column>{{ __('Total') }}</flux:table.column>
            <flux:table.column>{{ __('Metode') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column>{{ __('Aksi') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($transactions as $transaction)
                <flux:table.row wire:key="tx-{{ $transaction->id }}">
                    <flux:table.cell>
                        <span class="font-mono text-sm font-medium">{{ $transaction->invoice_number }}</span>
                    </flux:table.cell>
                    <flux:table.cell>{{ $transaction->created_at->format('d M Y H:i') }}</flux:table.cell>
                    <flux:table.cell>{{ $transaction->cashier->name }}</flux:table.cell>
                    <flux:table.cell class="font-semibold">Rp {{ number_format($transaction->total_amount / 100, 0, ',', '.') }}</flux:table.cell>
                    <flux:table.cell>{{ $transaction->payment_method?->label() ?? '-' }}</flux:table.cell>
                    <flux:table.cell>
                        @switch($transaction->status)
                            @case(\App\Enums\TransactionStatus::Completed)
                                <flux:badge color="green" size="sm">{{ $transaction->status->label() }}</flux:badge>
                                @break
                            @case(\App\Enums\TransactionStatus::Voided)
                                <flux:badge color="red" size="sm">{{ $transaction->status->label() }}</flux:badge>
                                @break
                            @default
                                <flux:badge color="zinc" size="sm">{{ $transaction->status->label() }}</flux:badge>
                        @endswitch
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($transaction->isCompleted())
                            <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="voidTransaction({{ $transaction->id }})" wire:confirm="{{ __('Yakin ingin membatalkan transaksi ini? Stok akan dikembalikan.') }}" />
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center py-8">
                        <flux:text>{{ __('Belum ada transaksi.') }}</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $transactions->links() }}
    </div>
</div>

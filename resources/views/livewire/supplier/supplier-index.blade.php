<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Supplier') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Kelola data supplier / distributor obat.') }}</flux:text>
        </div>
        <flux:button variant="primary" icon="plus" :href="route('suppliers.create')" wire:navigate>
            {{ __('Tambah Supplier') }}
        </flux:button>
    </div>

    @if (session('success'))
        <flux:callout variant="success" icon="check-circle" class="mb-4">
            {{ session('success') }}
        </flux:callout>
    @endif

    <div class="mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Cari nama supplier...') }}" icon="magnifying-glass" />
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Nama') }}</flux:table.column>
            <flux:table.column>{{ __('Kontak') }}</flux:table.column>
            <flux:table.column>{{ __('Telepon') }}</flux:table.column>
            <flux:table.column>{{ __('Email') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column>{{ __('Aksi') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($suppliers as $supplier)
                <flux:table.row wire:key="supplier-{{ $supplier->id }}">
                    <flux:table.cell class="font-medium">{{ $supplier->name }}</flux:table.cell>
                    <flux:table.cell>{{ $supplier->contact_person ?? '-' }}</flux:table.cell>
                    <flux:table.cell>{{ $supplier->phone ?? '-' }}</flux:table.cell>
                    <flux:table.cell>{{ $supplier->email ?? '-' }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($supplier->is_active)
                            <flux:badge color="green" size="sm">{{ __('Aktif') }}</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">{{ __('Nonaktif') }}</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-1">
                            <flux:button size="sm" variant="ghost" icon="pencil-square" :href="route('suppliers.edit', $supplier)" wire:navigate />
                            <flux:button size="sm" variant="ghost" :icon="$supplier->is_active ? 'x-mark' : 'check'" wire:click="toggleActive({{ $supplier->id }})" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-8">
                        <flux:text>{{ __('Belum ada supplier.') }}</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $suppliers->links() }}
    </div>
</div>

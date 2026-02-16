<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Kelola Tenant') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Kelola semua apotek yang terdaftar di PharmaOS.') }}</flux:text>
        </div>
        <flux:button variant="primary" icon="plus" :href="route('admin.tenants.create')" wire:navigate>
            {{ __('Tambah Tenant') }}
        </flux:button>
    </div>

    @if (session('success'))
        <flux:callout variant="success" icon="check-circle" class="mb-4">
            {{ session('success') }}
        </flux:callout>
    @endif

    <div class="mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Cari nama/email tenant...') }}" icon="magnifying-glass" />
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Apotek') }}</flux:table.column>
            <flux:table.column>{{ __('Pemilik') }}</flux:table.column>
            <flux:table.column>{{ __('Plan') }}</flux:table.column>
            <flux:table.column>{{ __('Users') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column>{{ __('Aksi') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($tenants as $tenant)
                <flux:table.row wire:key="tenant-{{ $tenant->id }}">
                    <flux:table.cell>
                        <div class="font-medium">{{ $tenant->name }}</div>
                        <div class="text-xs text-zinc-500">{{ $tenant->email }}</div>
                    </flux:table.cell>
                    <flux:table.cell>{{ $tenant->owner_name }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($tenant->activeSubscription)
                            <flux:badge size="sm" color="blue">{{ $tenant->activeSubscription->plan->label() }}</flux:badge>
                        @else
                            <flux:badge size="sm" color="zinc">{{ __('Tidak Ada') }}</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ $tenant->users_count }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($tenant->is_active)
                            <flux:badge color="green" size="sm">{{ __('Aktif') }}</flux:badge>
                        @else
                            <flux:badge color="red" size="sm">{{ __('Nonaktif') }}</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-1">
                            <flux:button size="sm" variant="ghost" icon="pencil-square" :href="route('admin.tenants.edit', $tenant)" wire:navigate />
                            <flux:button size="sm" variant="ghost" icon="credit-card" :href="route('admin.tenants.subscription', $tenant)" wire:navigate />
                            <flux:button size="sm" variant="ghost" :icon="$tenant->is_active ? 'x-mark' : 'check'" wire:click="toggleActive({{ $tenant->id }})" wire:confirm="{{ $tenant->is_active ? __('Nonaktifkan tenant ini?') : __('Aktifkan tenant ini?') }}" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-8">
                        <flux:text>{{ __('Belum ada tenant terdaftar.') }}</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">{{ $tenants->links() }}</div>
</div>

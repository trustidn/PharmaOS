<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ __('Kelola Langganan') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Tenant: :name', ['name' => $tenant->name]) }}</flux:text>
    </div>

    @if (session('success'))
        <flux:callout variant="success" icon="check-circle" class="mb-4">
            {{ session('success') }}
        </flux:callout>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Current Subscription --}}
        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Langganan Saat Ini') }}</flux:heading>
            @if ($tenant->activeSubscription)
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <flux:text>{{ __('Paket') }}</flux:text>
                        <flux:badge color="blue">{{ $tenant->activeSubscription->plan->label() }}</flux:badge>
                    </div>
                    <div class="flex justify-between">
                        <flux:text>{{ __('Status') }}</flux:text>
                        <flux:badge :color="$tenant->activeSubscription->isUsable() ? 'green' : 'red'">{{ $tenant->activeSubscription->status->label() }}</flux:badge>
                    </div>
                    <div class="flex justify-between">
                        <flux:text>{{ __('Mulai') }}</flux:text>
                        <span>{{ $tenant->activeSubscription->starts_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <flux:text>{{ __('Berakhir') }}</flux:text>
                        <span>{{ $tenant->activeSubscription->ends_at?->format('d M Y') ?? '-' }}</span>
                    </div>
                    <flux:separator />
                    <div class="flex justify-between">
                        <flux:text>{{ __('Max Produk') }}</flux:text>
                        <span>{{ $tenant->activeSubscription->max_products === PHP_INT_MAX ? 'Unlimited' : number_format($tenant->activeSubscription->max_products) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <flux:text>{{ __('Max Users') }}</flux:text>
                        <span>{{ $tenant->activeSubscription->max_users === PHP_INT_MAX ? 'Unlimited' : number_format($tenant->activeSubscription->max_users) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <flux:text>{{ __('Max Transaksi/bulan') }}</flux:text>
                        <span>{{ $tenant->activeSubscription->max_transactions_per_month === PHP_INT_MAX ? 'Unlimited' : number_format($tenant->activeSubscription->max_transactions_per_month) }}</span>
                    </div>
                </div>
            @else
                <flux:text class="py-4 text-center">{{ __('Belum ada langganan aktif.') }}</flux:text>
            @endif
        </flux:card>

        {{-- Update Subscription --}}
        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Ubah Langganan') }}</flux:heading>
            <form wire:submit="updateSubscription" class="space-y-4">
                <flux:field>
                    <flux:label>{{ __('Paket') }}</flux:label>
                    <flux:select wire:model="plan">
                        @foreach ($plans as $p)
                            <flux:select.option :value="$p->value">{{ $p->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Status') }}</flux:label>
                    <flux:select wire:model="status">
                        @foreach ($statuses as $s)
                            <flux:select.option :value="$s->value">{{ $s->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
                <flux:button type="submit" variant="primary" class="w-full">
                    {{ __('Perbarui Langganan') }}
                </flux:button>
            </form>
        </flux:card>
    </div>

    <div class="mt-4">
        <flux:button variant="ghost" icon="arrow-left" :href="route('admin.tenants')" wire:navigate>
            {{ __('Kembali ke Daftar Tenant') }}
        </flux:button>
    </div>
</div>

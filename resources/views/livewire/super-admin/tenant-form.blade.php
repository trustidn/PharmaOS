<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ $tenant ? __('Edit Tenant') : __('Tambah Tenant Baru') }}</flux:heading>
        <flux:text class="mt-1">{{ $tenant ? __('Perbarui informasi tenant.') : __('Buat apotek baru dengan akun owner.') }}</flux:text>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Informasi Apotek') }}</flux:heading>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:field class="sm:col-span-2">
                    <flux:label>{{ __('Nama Apotek') }}</flux:label>
                    <flux:input wire:model="name" required />
                    <flux:error name="name" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Nama Pemilik') }}</flux:label>
                    <flux:input wire:model="owner_name" required />
                    <flux:error name="owner_name" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Email Apotek') }}</flux:label>
                    <flux:input type="email" wire:model="email" required />
                    <flux:error name="email" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Telepon') }}</flux:label>
                    <flux:input wire:model="phone" />
                    <flux:error name="phone" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('No. SIA/SIPA') }}</flux:label>
                    <flux:input wire:model="license_number" />
                    <flux:error name="license_number" />
                </flux:field>
                <flux:field class="sm:col-span-2">
                    <flux:label>{{ __('Alamat') }}</flux:label>
                    <flux:textarea wire:model="address" rows="2" />
                    <flux:error name="address" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Paket Langganan') }}</flux:label>
                    <flux:select wire:model="plan">
                        @foreach ($plans as $p)
                            <flux:select.option :value="$p->value">{{ $p->label() }} - Rp {{ number_format($p->monthlyPrice() / 100, 0, ',', '.') }}/bulan</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plan" />
                </flux:field>
            </div>
        </flux:card>

        @unless ($tenant)
            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Akun Owner') }}</flux:heading>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('Email Login') }}</flux:label>
                        <flux:input type="email" wire:model="owner_email" required />
                        <flux:error name="owner_email" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Password') }}</flux:label>
                        <flux:input type="password" wire:model="owner_password" required />
                        <flux:error name="owner_password" />
                    </flux:field>
                </div>
            </flux:card>
        @endunless

        <div class="flex items-center gap-3">
            <flux:button type="submit" variant="primary">
                {{ $tenant ? __('Simpan Perubahan') : __('Buat Tenant') }}
            </flux:button>
            <flux:button variant="ghost" :href="route('admin.tenants')" wire:navigate>
                {{ __('Batal') }}
            </flux:button>
        </div>
    </form>
</div>

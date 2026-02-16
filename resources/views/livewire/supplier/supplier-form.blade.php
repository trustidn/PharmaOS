<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ $supplier ? __('Edit Supplier') : __('Tambah Supplier Baru') }}</flux:heading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:card>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:field class="sm:col-span-2">
                    <flux:label>{{ __('Nama Supplier') }}</flux:label>
                    <flux:input wire:model="name" placeholder="PT Kimia Farma" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Contact Person') }}</flux:label>
                    <flux:input wire:model="contact_person" />
                    <flux:error name="contact_person" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Telepon') }}</flux:label>
                    <flux:input wire:model="phone" type="tel" />
                    <flux:error name="phone" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Email') }}</flux:label>
                    <flux:input wire:model="email" type="email" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field class="sm:col-span-2">
                    <flux:label>{{ __('Alamat') }}</flux:label>
                    <flux:textarea wire:model="address" rows="2" />
                    <flux:error name="address" />
                </flux:field>
            </div>
        </flux:card>

        <div class="flex items-center gap-3">
            <flux:button type="submit" variant="primary">
                {{ $supplier ? __('Simpan Perubahan') : __('Tambah Supplier') }}
            </flux:button>
            <flux:button variant="ghost" :href="route('suppliers.index')" wire:navigate>
                {{ __('Batal') }}
            </flux:button>
        </div>
    </form>
</div>

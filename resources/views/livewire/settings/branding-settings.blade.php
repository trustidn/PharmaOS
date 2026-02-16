<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ __('Pengaturan Branding') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Sesuaikan tampilan aplikasi dengan identitas apotek Anda.') }}</flux:text>
    </div>

    @if (session('success'))
        <flux:callout variant="success" icon="check-circle" class="mb-4">
            {{ session('success') }}
        </flux:callout>
    @endif

    <form wire:submit="save" class="space-y-6">
        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Nama Apotek') }}</flux:heading>
            <flux:field>
                <flux:label>{{ __('Nama tampilan apotek') }}</flux:label>
                <flux:input wire:model="name" placeholder="{{ __('Contoh: Apotek Sehat') }}" />
                <flux:error name="name" />
                <flux:text size="sm" class="mt-1">{{ __('Nama ini tampil di logo/sidebar aplikasi.') }}</flux:text>
            </flux:field>
        </flux:card>

        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Informasi Apotek (di struk)') }}</flux:heading>
            <flux:field>
                <flux:label>{{ __('Alamat') }}</flux:label>
                <flux:textarea wire:model="address" rows="2" placeholder="{{ __('Alamat apotek') }}" />
                <flux:error name="address" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('No. HP / Telepon') }}</flux:label>
                <flux:input wire:model="phone" placeholder="08xx atau (021) xxx" />
                <flux:error name="phone" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Website') }}</flux:label>
                <flux:input wire:model="website" placeholder="https://apotek.example.com" />
                <flux:error name="website" />
            </flux:field>
        </flux:card>

        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Logo') }}</flux:heading>

            @if ($current_logo)
                <div class="mb-4">
                    <flux:text size="sm" class="mb-2">{{ __('Logo saat ini:') }}</flux:text>
                    <img src="{{ Storage::url($current_logo) }}" alt="Logo" class="h-16 w-auto rounded-lg border p-2 dark:border-zinc-700" />
                </div>
            @endif

            <flux:field>
                <flux:label>{{ __('Upload Logo Baru') }}</flux:label>
                <flux:input type="file" wire:model="logo" accept="image/*" />
                <flux:error name="logo" />
                <flux:text size="sm" class="mt-1">{{ __('Format: JPG, PNG, SVG. Maks: 2MB.') }}</flux:text>
            </flux:field>
        </flux:card>

        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Warna') }}</flux:heading>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Warna Utama') }}</flux:label>
                    <div class="flex items-center gap-3">
                        <input type="color" wire:model.live="primary_color" class="h-10 w-14 cursor-pointer rounded-lg border border-zinc-200 dark:border-zinc-700" />
                        <flux:input wire:model.live="primary_color" class="flex-1" placeholder="#3B82F6" />
                    </div>
                    <flux:error name="primary_color" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Warna Sekunder') }}</flux:label>
                    <div class="flex items-center gap-3">
                        <input type="color" wire:model.live="secondary_color" class="h-10 w-14 cursor-pointer rounded-lg border border-zinc-200 dark:border-zinc-700" />
                        <flux:input wire:model.live="secondary_color" class="flex-1" placeholder="#1E40AF" />
                    </div>
                    <flux:error name="secondary_color" />
                </flux:field>
            </div>

            {{-- Preview --}}
            <div class="mt-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text size="sm" class="mb-2">{{ __('Preview:') }}</flux:text>
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-lg" style="background-color: {{ $primary_color }}"></div>
                    <div class="h-10 w-10 rounded-lg" style="background-color: {{ $secondary_color }}"></div>
                    <flux:text size="sm">{{ $primary_color }} / {{ $secondary_color }}</flux:text>
                </div>
            </div>
        </flux:card>

        <flux:button type="submit" variant="primary">
            {{ __('Simpan Branding') }}
        </flux:button>
    </form>
</div>

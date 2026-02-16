<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ __('Pengaturan Aplikasi') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Nama, logo, dan favicon aplikasi (tampil di halaman welcome dan judul browser).') }}</flux:text>
    </div>

    @if (session('success'))
        <flux:callout variant="success" icon="check-circle" class="mb-4">
            {{ session('success') }}
        </flux:callout>
    @endif

    <form wire:submit="save" class="space-y-6">
        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Nama & Tagline') }}</flux:heading>
            <flux:field>
                <flux:label>{{ __('Nama Aplikasi') }}</flux:label>
                <flux:input wire:model="app_name" placeholder="PharmaOS" />
                <flux:error name="app_name" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Tagline (opsional)') }}</flux:label>
                <flux:input wire:model="tagline" placeholder="{{ __('Sistem manajemen apotek') }}" />
                <flux:error name="tagline" />
            </flux:field>
        </flux:card>

        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Logo') }}</flux:heading>
            @if ($current_logo_url)
                <div class="mb-4">
                    <flux:text size="sm" class="mb-2">{{ __('Logo saat ini:') }}</flux:text>
                    <img src="{{ $current_logo_url }}" alt="Logo" class="h-16 w-auto rounded-lg border p-2 dark:border-zinc-700" />
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
            <flux:heading size="lg" class="mb-4">{{ __('Favicon') }}</flux:heading>
            @if ($current_favicon_url)
                <div class="mb-4">
                    <flux:text size="sm" class="mb-2">{{ __('Favicon saat ini:') }}</flux:text>
                    <img src="{{ $current_favicon_url }}" alt="Favicon" class="h-8 w-8 rounded border p-1 dark:border-zinc-700" />
                </div>
            @endif
            <flux:field>
                <flux:label>{{ __('Upload Favicon Baru') }}</flux:label>
                <flux:input type="file" wire:model="favicon" accept="image/*" />
                <flux:error name="favicon" />
                <flux:text size="sm" class="mt-1">{{ __('Ikon tab browser. PNG/ICO. Maks: 512KB.') }}</flux:text>
            </flux:field>
        </flux:card>

        <flux:button type="submit" variant="primary">{{ __('Simpan') }}</flux:button>
    </form>
</div>

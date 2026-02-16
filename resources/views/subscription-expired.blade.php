<x-layouts::app :title="__('Langganan Tidak Aktif')">
    <div class="flex h-full items-center justify-center">
        <div class="max-w-md text-center">
            <flux:icon name="exclamation-triangle" class="mx-auto h-16 w-16 text-amber-500" />
            <flux:heading size="xl" class="mt-4">{{ __('Langganan Tidak Aktif') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('Langganan apotek Anda telah berakhir atau tidak aktif. Silakan hubungi administrator untuk memperbarui langganan Anda.') }}
            </flux:text>
            <div class="mt-6">
                <flux:button variant="primary" :href="route('dashboard')" wire:navigate>
                    {{ __('Kembali ke Dashboard') }}
                </flux:button>
            </div>
        </div>
    </div>
</x-layouts::app>

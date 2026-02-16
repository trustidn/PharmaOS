<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ $batch ? __('Edit Batch') : __('Tambah Batch Baru') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Produk: :product (:sku)', ['product' => $product->name, 'sku' => $product->sku]) }}</flux:text>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Informasi Batch') }}</flux:heading>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Nomor Batch') }}</flux:label>
                    <flux:input wire:model="batch_number" placeholder="BATCH-001" required />
                    <flux:error name="batch_number" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Harga Beli per Satuan (Rp)') }}</flux:label>
                    <flux:input type="text" wire:model="purchase_price_rupiah" placeholder="0 atau 2500,5" inputmode="decimal" />
                    <flux:description>{{ __('Gunakan koma untuk desimal, misal: 2500,2') }}</flux:description>
                    <flux:error name="purchase_price_rupiah" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Jumlah Diterima') }}</flux:label>
                    <flux:input type="number" wire:model="quantity_received" min="1" required :disabled="(bool) $batch" />
                    <flux:error name="quantity_received" />
                    @if ($batch)
                        <flux:text size="sm" class="mt-1">{{ __('Jumlah tidak dapat diubah setelah batch dibuat.') }}</flux:text>
                    @endif
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Tanggal Diterima') }}</flux:label>
                    <flux:input type="date" wire:model="received_at" required />
                    <flux:error name="received_at" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Tanggal Kadaluarsa') }}</flux:label>
                    <flux:input type="date" wire:model="expired_at" required />
                    <flux:error name="expired_at" />
                </flux:field>
            </div>
        </flux:card>

        <div class="flex items-center gap-3">
            <flux:button type="submit" variant="primary">
                {{ $batch ? __('Simpan Perubahan') : __('Tambah Batch') }}
            </flux:button>
            <flux:button variant="ghost" :href="route('inventory.batches', $product)" wire:navigate>
                {{ __('Batal') }}
            </flux:button>
        </div>
    </form>
</div>

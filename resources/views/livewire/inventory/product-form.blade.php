<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ $product ? __('Edit Produk') : __('Tambah Produk Baru') }}</flux:heading>
        <flux:text class="mt-1">{{ $product ? __('Perbarui informasi produk.') : __('Isi data produk baru untuk apotek Anda.') }}</flux:text>
    </div>

    @if ($errors->has('limit'))
        <flux:callout variant="danger" icon="exclamation-triangle" class="mb-4">
            {{ $errors->first('limit') }}
        </flux:callout>
    @endif

    <form wire:submit="save" class="space-y-6">
        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Informasi Dasar') }}</flux:heading>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('SKU / Kode Produk') }}</flux:label>
                    <flux:input wire:model="sku" placeholder="MED-0001" required />
                    <flux:error name="sku" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Barcode') }}</flux:label>
                    <flux:input wire:model="barcode" placeholder="{{ __('Opsional') }}" />
                    <flux:error name="barcode" />
                </flux:field>

                <flux:field class="sm:col-span-2">
                    <flux:label>{{ __('Nama Produk') }}</flux:label>
                    <flux:input wire:model="name" placeholder="{{ __('Paracetamol 500mg') }}" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field class="sm:col-span-2">
                    <flux:label>{{ __('Nama Generik') }}</flux:label>
                    <flux:input wire:model="generic_name" placeholder="{{ __('Opsional') }}" />
                    <flux:error name="generic_name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Kategori') }}</flux:label>
                    <flux:select wire:model="category_id" placeholder="{{ __('Pilih Kategori') }}">
                        <flux:select.option value="">{{ __('Tanpa Kategori') }}</flux:select.option>
                        @foreach ($categories as $category)
                            <flux:select.option :value="$category->id">{{ $category->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="category_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Satuan Dasar') }}</flux:label>
                    <flux:select wire:model="unit_id" placeholder="{{ __('Pilih Satuan') }}" required>
                        @foreach ($units as $unit)
                            <flux:select.option :value="$unit->id">{{ $unit->name }} ({{ $unit->abbreviation }})</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="unit_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Base Unit (Satuan Terkecil)') }}</flux:label>
                    <flux:input wire:model="base_unit" placeholder="pcs, Butir, ml" />
                    <flux:description>{{ __('Nama satuan terkecil untuk stok, misal: Butir, pcs') }}</flux:description>
                    <flux:error name="base_unit" />
                </flux:field>
            </div>
        </flux:card>

        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Satuan Bertingkat (Multi-Unit)') }}</flux:heading>
            <flux:text class="mb-4">{{ __('Tambahkan satuan jual lain dengan faktor konversi ke base unit. Contoh: 1 Strip = 10 Butir → isi Nama "Strip", Conversion Factor 10.') }}</flux:text>

            <flux:table class="mb-4">
                <flux:table.columns>
                    <flux:table.column>{{ __('Nama Satuan') }}</flux:table.column>
                    <flux:table.column>{{ __('Conversion Factor') }}</flux:table.column>
                    <flux:table.column>{{ __('Harga Jual (Rp)') }}</flux:table.column>
                    <flux:table.column>{{ __('Barcode') }}</flux:table.column>
                    <flux:table.column class="w-20">{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($productUnits as $index => $unit)
                        <flux:table.row wire:key="product-unit-{{ $index }}">
                            <flux:table.cell>
                                <flux:input wire:model="productUnits.{{ $index }}.name" placeholder="Strip, Box" class="min-w-24" />
                                @error('productUnits.'.$index.'.name')
                                    <flux:error class="mt-1">{{ $message }}</flux:error>
                                @enderror
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:input type="number" wire:model="productUnits.{{ $index }}.conversion_factor" min="2" placeholder="10" class="w-20" />
                                @error('productUnits.'.$index.'.conversion_factor')
                                    <flux:error class="mt-1">{{ $message }}</flux:error>
                                @enderror
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:input type="text" wire:model="productUnits.{{ $index }}.price_sell_rupiah" placeholder="0 atau 2500,5" inputmode="decimal" class="w-28" />
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:input wire:model="productUnits.{{ $index }}.barcode" placeholder="{{ __('Opsional') }}" class="min-w-24" />
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button type="button" variant="ghost" icon="x-mark" icon-position="left" wire:click="removeProductUnit({{ $index }})" class="text-red-600">
                                    {{ __('Hapus') }}
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
            <flux:button type="button" variant="outline" icon="plus" wire:click="addProductUnit">
                {{ __('Tambah Satuan') }}
            </flux:button>
        </flux:card>

        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Harga & Stok') }}</flux:heading>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Harga Jual (Rp)') }}</flux:label>
                    <flux:input type="text" wire:model="selling_price_rupiah" placeholder="0 atau 2500,5" inputmode="decimal" />
                    <flux:description>{{ __('Gunakan koma untuk desimal, misal: 2500,2') }}</flux:description>
                    <flux:error name="selling_price_rupiah" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Stok Minimum') }}</flux:label>
                    <flux:input type="number" wire:model="min_stock" min="0" required />
                    <flux:error name="min_stock" />
                </flux:field>

                <flux:field>
                    <flux:switch wire:model="requires_prescription" label="{{ __('Memerlukan Resep Dokter') }}" />
                </flux:field>
            </div>
        </flux:card>

        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Deskripsi') }}</flux:heading>
            <flux:textarea wire:model="description" placeholder="{{ __('Deskripsi produk (opsional)...') }}" rows="3" />
        </flux:card>

        <div class="flex items-center gap-3">
            <flux:button type="submit" variant="primary">
                {{ $product ? __('Simpan Perubahan') : __('Tambah Produk') }}
            </flux:button>
            <flux:button variant="ghost" :href="route('inventory.products')" wire:navigate>
                {{ __('Batal') }}
            </flux:button>
        </div>
    </form>
</div>

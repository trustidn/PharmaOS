<div
    x-data="{
        focusSearch() { $refs.searchInput?.focus() },
        printListenerRegistered: false
    }"
    x-init="if (!printListenerRegistered) { printListenerRegistered = true; $wire.on('print-receipt', () => window.print()); }"
    x-on:keydown.f2.window.prevent="focusSearch()"
    x-on:keydown.f8.window.prevent="$wire.openPayment()"
    x-on:keydown.escape.window="$wire.showPaymentModal = false"
>

    <div class="mb-4 flex items-center justify-between">
        <flux:heading size="xl">{{ __('Kasir / POS') }}</flux:heading>
        <div class="flex items-center gap-2 text-sm text-zinc-500">
            <flux:badge size="sm" color="zinc">F2</flux:badge> {{ __('Cari') }}
            <flux:badge size="sm" color="zinc">F8</flux:badge> {{ __('Bayar') }}
            <flux:badge size="sm" color="zinc">Esc</flux:badge> {{ __('Tutup') }}
        </div>
    </div>

    @if ($errors->any())
        <flux:callout variant="danger" icon="exclamation-triangle" class="mb-4">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </flux:callout>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left: Product search + Cart --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Search --}}
            <div class="relative">
                <flux:input
                    x-ref="searchInput"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('Cari produk (nama, SKU, barcode)...') }}"
                    icon="magnifying-glass"
                    autofocus
                />
                @if (!empty($searchResults))
                    <div class="absolute z-50 mt-1 w-full rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                        @foreach ($searchResults as $result)
                            <button
                                wire:click="selectProductForCart({{ $result['id'] }})"
                                wire:key="search-{{ $result['id'] }}"
                                class="flex w-full items-center justify-between px-4 py-3 text-left hover:bg-zinc-50 dark:hover:bg-zinc-700 first:rounded-t-lg last:rounded-b-lg"
                            >
                                <div>
                                    <div class="font-medium">{{ $result['name'] }}</div>
                                    <div class="text-xs text-zinc-500">{{ $result['sku'] }} · {{ $result['base_unit'] ?? 'pcs' }}</div>
                                </div>
                                <flux:badge size="sm" color="blue">Rp {{ number_format($result['selling_price'] / 100, 0, ',', '.') }}/{{ $result['base_unit'] ?? 'pcs' }}</flux:badge>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Cart Table --}}
            <flux:card class="p-0 overflow-hidden">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>#</flux:table.column>
                        <flux:table.column>{{ __('Produk') }}</flux:table.column>
                        <flux:table.column>{{ __('Satuan') }}</flux:table.column>
                        <flux:table.column>{{ __('Harga') }}</flux:table.column>
                        <flux:table.column>{{ __('Qty') }}</flux:table.column>
                        <flux:table.column>{{ __('Diskon') }}</flux:table.column>
                        <flux:table.column>{{ __('Subtotal') }}</flux:table.column>
                        <flux:table.column></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($cart as $index => $item)
                            <flux:table.row wire:key="cart-{{ $index }}">
                                <flux:table.cell>{{ $index + 1 }}</flux:table.cell>
                                <flux:table.cell>
                                    <div class="font-medium">{{ $item['name'] }}</div>
                                    <div class="text-xs text-zinc-500">{{ $item['sku'] }}</div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="zinc">{{ $item['unit_name'] ?? 'pcs' }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>Rp {{ number_format($item['unit_price'] / 100, 0, ',', '.') }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:input type="number" min="1" class="w-20" value="{{ $item['quantity'] }}" wire:change="updateQuantity({{ $index }}, $event.target.value)" />
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:input type="text" inputmode="decimal" class="w-24" placeholder="0" value="{{ format_rupiah_input($item['discount']) }}" wire:change="updateItemDiscount({{ $index }}, $event.target.value)" />
                                </flux:table.cell>
                                <flux:table.cell class="font-semibold">Rp {{ number_format($item['subtotal'] / 100, 0, ',', '.') }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="removeFromCart({{ $index }})" />
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="8" class="text-center py-12">
                                    <flux:icon name="shopping-cart" class="mx-auto h-12 w-12 text-zinc-300 dark:text-zinc-600" />
                                    <flux:text class="mt-2">{{ __('Keranjang masih kosong. Cari produk untuk memulai.') }}</flux:text>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </div>

        {{-- Right: Summary --}}
        <div class="space-y-4">
            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Ringkasan') }}</flux:heading>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <flux:text>{{ __('Subtotal') }}</flux:text>
                        <span class="font-medium">Rp {{ number_format($this->subtotal / 100, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <flux:text>{{ __('Diskon (Rp)') }}</flux:text>
                        <flux:input type="text" inputmode="decimal" placeholder="0" class="w-32 text-right" value="{{ format_rupiah_input($this->discountAmount) }}" wire:input="setDiscountAmountFromRupiah($event.target.value)" />
                    </div>

                    <flux:separator />

                    <div class="flex justify-between text-lg font-bold">
                        <span>{{ __('Total') }}</span>
                        <span class="text-blue-600 dark:text-blue-400">Rp {{ number_format($this->total / 100, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="mt-4 space-y-2">
                    <flux:textarea wire:model="notes" placeholder="{{ __('Catatan (opsional)...') }}" rows="2" />
                </div>

                <div class="mt-4 flex gap-2">
                    <flux:button variant="primary" class="flex-1" wire:click="openPayment" :disabled="empty($cart)">
                        {{ __('Bayar (F8)') }}
                    </flux:button>
                    <flux:button variant="ghost" wire:click="clearCart" :disabled="empty($cart)">
                        {{ __('Batal') }}
                    </flux:button>
                </div>
            </flux:card>

            {{-- Last Transaction Receipt --}}
            @if ($lastTransaction)
                <flux:card class="border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-950/20">
                    <flux:heading size="sm" class="text-green-700 dark:text-green-400">{{ __('Transaksi Terakhir') }}</flux:heading>
                    <div class="mt-2 space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span>{{ __('Invoice') }}</span>
                            <span class="font-mono font-medium">{{ $lastTransaction['invoice_number'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>{{ __('Total') }}</span>
                            <span class="font-semibold">Rp {{ number_format($lastTransaction['total_amount'] / 100, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>{{ __('Bayar') }}</span>
                            <span>Rp {{ number_format($lastTransaction['amount_paid'] / 100, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>{{ __('Kembalian') }}</span>
                            <span class="font-semibold text-green-600">Rp {{ number_format($lastTransaction['change_amount'] / 100, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>{{ __('Metode') }}</span>
                            <span>{{ $lastTransaction['payment_method'] }}</span>
                        </div>
                        @if (!empty($lastTransaction['buyer_name']) || !empty($lastTransaction['buyer_phone']))
                            <div class="flex justify-between">
                                <span>{{ __('Pembeli') }}</span>
                                <span class="text-right">{{ $lastTransaction['buyer_name'] ?? '-' }}@if (!empty($lastTransaction['buyer_phone'])) · {{ $lastTransaction['buyer_phone'] }}@endif</span>
                            </div>
                        @endif
                    </div>
                </flux:card>
            @endif
        </div>
    </div>

    {{-- Payment Modal --}}
    <flux:modal wire:model="showPaymentModal" class="max-w-md">
        <flux:heading size="lg">{{ __('Pembayaran') }}</flux:heading>

        <div class="mt-4 space-y-4">
            <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800 text-center">
                <flux:text size="sm">{{ __('Total yang harus dibayar') }}</flux:text>
                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                    Rp {{ number_format($this->total / 100, 0, ',', '.') }}
                </div>
            </div>

            <flux:field>
                <flux:label>{{ __('Pembeli (opsional)') }}</flux:label>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <flux:input wire:model="buyerName" placeholder="{{ __('Nama pembeli') }}" />
                    <flux:input wire:model="buyerPhone" type="tel" placeholder="{{ __('No. telepon') }}" />
                </div>
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Metode Pembayaran') }}</flux:label>
                <flux:select wire:model.live="paymentMethod">
                    @foreach (\App\Enums\PaymentMethod::cases() as $method)
                        <flux:select.option :value="$method->value">{{ $method->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Jumlah Bayar (Rp)') }}</flux:label>
                <flux:input type="text" inputmode="decimal" placeholder="0 atau 2500,5" value="{{ format_rupiah_input($this->amountPaid) }}" wire:input="setAmountPaidFromRupiah($event.target.value)" />
                <flux:error name="amountPaid" />
            </flux:field>

            @if ($this->amountPaid >= $this->total)
                <div class="rounded-lg bg-green-50 p-3 dark:bg-green-950/20 text-center">
                    <flux:text size="sm">{{ __('Kembalian') }}</flux:text>
                    <div class="text-2xl font-bold text-green-600">
                        Rp {{ number_format($this->change / 100, 0, ',', '.') }}
                    </div>
                </div>
            @endif

            <div class="flex gap-2 pt-2">
                <flux:button variant="primary" class="flex-1" wire:click="processSale" :disabled="$amountPaid < $this->total">
                    {{ __('Proses Pembayaran') }}
                </flux:button>
                <flux:button variant="ghost" wire:click="$set('showPaymentModal', false)">
                    {{ __('Batal') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Unit picker modal (multi-unit products) --}}
    <flux:modal wire:model="showUnitModal" class="max-w-md">
        <flux:heading size="lg">{{ __('Pilih Satuan') }}</flux:heading>
        @if ($selectedProductForUnit)
            <flux:text class="mt-1">{{ $selectedProductForUnit['name'] }} ({{ $selectedProductForUnit['sku'] }})</flux:text>

            <flux:field class="mt-4">
                <flux:label>{{ __('Satuan jual') }}</flux:label>
                <flux:radio.group wire:model.live="selectedUnitKey" variant="buttons" class="flex flex-col gap-2">
                    <flux:radio value="base" variant="buttons">
                        {{ $selectedProductForUnit['base_unit'] ?? 'pcs' }} — Rp {{ number_format(($selectedProductForUnit['selling_price'] ?? 0) / 100, 0, ',', '.') }}/{{ $selectedProductForUnit['base_unit'] ?? 'pcs' }}
                    </flux:radio>
                    @foreach ($selectedProductForUnit['product_units'] ?? [] as $pu)
                        <flux:radio value="{{ $pu['id'] }}" variant="buttons">
                            {{ $pu['name'] }} (1 {{ $pu['name'] }} = {{ $pu['conversion_factor'] }} {{ $selectedProductForUnit['base_unit'] ?? 'pcs' }}) — Rp {{ number_format(($pu['price_sell'] ?? 0) / 100, 0, ',', '.') }}/{{ $pu['name'] }}
                        </flux:radio>
                    @endforeach
                </flux:radio.group>
            </flux:field>

            <flux:field class="mt-4">
                <flux:label>{{ __('Jumlah') }}</flux:label>
                <flux:input type="number" wire:model.live="unitQuantityToAdd" min="1" />
            </flux:field>

            <div class="mt-4 flex gap-2">
                <flux:button variant="primary" class="flex-1" wire:click="addToCartWithUnitFromModal">
                    {{ __('Tambah ke Keranjang') }}
                </flux:button>
                <flux:button variant="ghost" wire:click="closeUnitModal">
                    {{ __('Batal') }}
                </flux:button>
            </div>
        @endif
    </flux:modal>

    {{-- Struk cetak (white-label). Hanya tampil saat print; di layar disembunyikan. --}}
    @if ($lastTransaction && $receiptBranding)
        <div
            id="receipt-print"
            class="receipt-for-print"
            style="visibility: hidden; position: fixed; left: -9999px; top: 0; width: 80mm; max-width: 100%; font-size: 12px; line-height: 1.3; color: #000; background: #fff; padding: 8px; font-family: ui-monospace, monospace; box-sizing: border-box;"
        >
            <div style="text-align: center; border-bottom: 2px solid {{ $receiptBranding['primary_color'] }}; padding-bottom: 6px; margin-bottom: 6px;">
                @if ($receiptBranding['logo_url'] ?? null)
                    <img src="{{ $receiptBranding['logo_url'] }}" alt="" style="max-height: 40px; max-width: 100%; margin-bottom: 4px;" />
                @endif
                <div style="font-weight: 700; font-size: 14px; color: {{ $receiptBranding['primary_color'] }};">{{ $receiptBranding['name'] }}</div>
                @if (!empty($receiptBranding['address']) || !empty($receiptBranding['phone']) || !empty($receiptBranding['website']))
                    <div style="font-size: 9px; color: #555; margin-top: 2px; line-height: 1.2;">
                        @if (!empty($receiptBranding['address'])){{ $receiptBranding['address'] }}@endif
                        @if (!empty($receiptBranding['phone']))<br />{{ $receiptBranding['phone'] }}@endif
                        @if (!empty($receiptBranding['website']))<br />{{ $receiptBranding['website'] }}@endif
                    </div>
                @endif
                <div style="font-size: 10px; color: #666;">{{ __('Struk Penjualan') }}</div>
            </div>
            <div style="margin-bottom: 6px;">
                <div>{{ __('No.') }} {{ $lastTransaction['invoice_number'] }}</div>
                <div>{{ $lastTransaction['completed_at'] }}</div>
                @if (!empty($lastTransaction['buyer_name']) || !empty($lastTransaction['buyer_phone']))
                    <div style="margin-top: 4px; font-size: 10px;">
                        @if (!empty($lastTransaction['buyer_name'])){{ __('Pembeli') }}: {{ $lastTransaction['buyer_name'] }}@endif
                        @if (!empty($lastTransaction['buyer_name']) && !empty($lastTransaction['buyer_phone'])) · @endif
                        @if (!empty($lastTransaction['buyer_phone'])){{ $lastTransaction['buyer_phone'] }}@endif
                    </div>
                @endif
            </div>
            <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 6px;">
                <thead>
                    <tr style="border-bottom: 1px dashed #999;">
                        <th style="text-align: left; padding: 2px 0;">{{ __('Item') }}</th>
                        <th style="text-align: right; padding: 2px 0;">{{ __('Qty') }}</th>
                        <th style="text-align: right; padding: 2px 0;">{{ __('Harga') }}</th>
                        <th style="text-align: right; padding: 2px 0;">{{ __('Subtotal') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lastTransaction['items'] ?? [] as $item)
                        <tr style="border-bottom: 1px dotted #ccc;">
                            <td style="padding: 2px 0; word-break: break-word;">{{ $item['name'] }} ({{ $item['unit_name'] }})</td>
                            <td style="text-align: right; padding: 2px 0;">{{ $item['quantity'] }}</td>
                            <td style="text-align: right; padding: 2px 0;">Rp {{ number_format($item['unit_price'] / 100, 0, ',', '.') }}</td>
                            <td style="text-align: right; padding: 2px 0;">Rp {{ number_format($item['subtotal'] / 100, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="border-top: 1px solid #333; padding-top: 4px; margin-top: 4px; font-size: 11px;">
                <div style="display: flex; justify-content: space-between;"><span>{{ __('Subtotal') }}</span><span>Rp {{ number_format(($lastTransaction['subtotal'] ?? 0) / 100, 0, ',', '.') }}</span></div>
                @if (($lastTransaction['discount_amount'] ?? 0) > 0)
                    <div style="display: flex; justify-content: space-between;"><span>{{ __('Diskon') }}</span><span>- Rp {{ number_format(($lastTransaction['discount_amount'] ?? 0) / 100, 0, ',', '.') }}</span></div>
                @endif
                <div style="display: flex; justify-content: space-between; font-weight: 700;"><span>{{ __('Total') }}</span><span>Rp {{ number_format(($lastTransaction['total_amount'] ?? 0) / 100, 0, ',', '.') }}</span></div>
                <div style="display: flex; justify-content: space-between;"><span>{{ __('Bayar') }} ({{ $lastTransaction['payment_method'] ?? '' }})</span><span>Rp {{ number_format(($lastTransaction['amount_paid'] ?? 0) / 100, 0, ',', '.') }}</span></div>
                @if (($lastTransaction['change_amount'] ?? 0) > 0)
                    <div style="display: flex; justify-content: space-between;"><span>{{ __('Kembalian') }}</span><span>Rp {{ number_format(($lastTransaction['change_amount'] ?? 0) / 100, 0, ',', '.') }}</span></div>
                @endif
            </div>
            <div style="text-align: center; margin-top: 8px; font-size: 10px; color: #666;">{{ __('Terima kasih') }}</div>
        </div>

        <style media="print">
            @page { size: auto; margin: 5mm; }
            body * { visibility: hidden; }
            #receipt-print, #receipt-print * { visibility: visible; }
            #receipt-print {
                position: absolute !important;
                left: 50% !important;
                top: 0 !important;
                transform: translateX(-50%) !important;
                width: 100% !important;
                max-width: 80mm !important;
                min-width: 0 !important;
                visibility: visible !important;
                box-shadow: none !important;
                background: #fff !important;
                box-sizing: border-box !important;
            }
        </style>
    @endif
</div>

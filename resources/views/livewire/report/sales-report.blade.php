<div
    x-data="{ printReceiptRegistered: false, printTableRegistered: false }"
    x-init="
        if (!printReceiptRegistered) { printReceiptRegistered = true; $wire.on('print-receipt', () => { document.getElementById('sales-receipt-print')?.classList.add('active-print'); document.getElementById('sales-table-print')?.classList.remove('active-print'); window.print(); setTimeout(() => document.getElementById('sales-receipt-print')?.classList.remove('active-print'), 100); }); }
        if (!printTableRegistered) { printTableRegistered = true; $wire.on('print-sales-table', () => { document.getElementById('sales-table-print')?.classList.add('active-print'); document.getElementById('sales-receipt-print')?.classList.remove('active-print'); window.print(); setTimeout(() => document.getElementById('sales-table-print')?.classList.remove('active-print'), 100); }); }
    "
>
    <div class="mb-6">
        <flux:heading size="xl">{{ __('Laporan Penjualan') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Ringkasan dan detail transaksi penjualan.') }}</flux:text>
    </div>

    {{-- Filters --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row">
        <flux:select wire:model.live="period" class="w-full sm:w-40">
            <flux:select.option value="today">{{ __('Hari Ini') }}</flux:select.option>
            <flux:select.option value="week">{{ __('Minggu Ini') }}</flux:select.option>
            <flux:select.option value="month">{{ __('Bulan Ini') }}</flux:select.option>
            <flux:select.option value="year">{{ __('Tahun Ini') }}</flux:select.option>
            <flux:select.option value="custom">{{ __('Kustom') }}</flux:select.option>
        </flux:select>
        @if ($period === 'custom')
            <flux:input type="date" wire:model.live="dateFrom" />
            <flux:input type="date" wire:model.live="dateTo" />
        @endif
    </div>

    {{-- Summary Cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card>
            <flux:text size="sm">{{ __('Total Transaksi') }}</flux:text>
            <div class="mt-1 text-2xl font-bold">{{ number_format($summary['total_transactions']) }}</div>
        </flux:card>
        <flux:card>
            <flux:text size="sm">{{ __('Total Pendapatan') }}</flux:text>
            <div class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">Rp {{ number_format($summary['total_revenue'] / 100, 0, ',', '.') }}</div>
        </flux:card>
        <flux:card>
            <flux:text size="sm">{{ __('Total Diskon') }}</flux:text>
            <div class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">Rp {{ number_format($summary['total_discount'] / 100, 0, ',', '.') }}</div>
        </flux:card>
        <flux:card>
            <flux:text size="sm">{{ __('Rata-rata Transaksi') }}</flux:text>
            <div class="mt-1 text-2xl font-bold">Rp {{ number_format($summary['average_transaction'] / 100, 0, ',', '.') }}</div>
        </flux:card>
    </div>

    {{-- Daily Sales --}}
    @if ($dailySales->isNotEmpty())
        <flux:card class="mb-6">
            <flux:heading size="lg" class="mb-4">{{ __('Penjualan Harian') }}</flux:heading>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Tanggal') }}</flux:table.column>
                    <flux:table.column>{{ __('Jumlah Transaksi') }}</flux:table.column>
                    <flux:table.column>{{ __('Total Penjualan') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($dailySales as $day)
                        <flux:table.row wire:key="day-{{ $day->date }}">
                            <flux:table.cell>{{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</flux:table.cell>
                            <flux:table.cell>{{ number_format($day->count) }}</flux:table.cell>
                            <flux:table.cell class="font-semibold">Rp {{ number_format($day->total / 100, 0, ',', '.') }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @endif

    {{-- Transaction Detail --}}
    <flux:card>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <flux:heading size="lg">{{ __('Detail Transaksi') }}</flux:heading>
            <flux:button size="sm" variant="outline" icon="printer" wire:click="openTablePrint" :disabled="$transactions->isEmpty()">
                {{ __('Cetak Tabel') }}
            </flux:button>
        </div>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Invoice') }}</flux:table.column>
                <flux:table.column>{{ __('Tanggal') }}</flux:table.column>
                <flux:table.column>{{ __('Pembeli') }}</flux:table.column>
                <flux:table.column>{{ __('No. Telp') }}</flux:table.column>
                <flux:table.column>{{ __('Kasir') }}</flux:table.column>
                <flux:table.column>{{ __('Total') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($transactions as $tx)
                    <flux:table.row wire:key="txr-{{ $tx->id }}">
                        <flux:table.cell class="font-mono text-sm">{{ $tx->invoice_number }}</flux:table.cell>
                        <flux:table.cell>{{ $tx->completed_at?->format('d M Y H:i') }}</flux:table.cell>
                        <flux:table.cell>{{ $tx->buyer_name ?? '–' }}</flux:table.cell>
                        <flux:table.cell>{{ $tx->buyer_phone ?? '–' }}</flux:table.cell>
                        <flux:table.cell>{{ $tx->cashier->name }}</flux:table.cell>
                        <flux:table.cell class="font-semibold">Rp {{ number_format($tx->total_amount / 100, 0, ',', '.') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:button size="sm" variant="ghost" icon="printer" wire:click="openReceiptPrint({{ $tx->id }})">
                                {{ __('Cetak Struk') }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-8">
                            <flux:text>{{ __('Tidak ada transaksi pada periode ini.') }}</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
        <div class="mt-3 flex items-center justify-end gap-2 border-t border-zinc-200 pt-3 dark:border-zinc-700">
            <flux:text size="sm" class="text-zinc-500">{{ __('Total (sesuai filter)') }}</flux:text>
            <span class="text-lg font-bold">Rp {{ number_format($summary['total_revenue'] / 100, 0, ',', '.') }}</span>
        </div>
        <div class="mt-4">{{ $transactions->links() }}</div>
    </flux:card>

    {{-- Struk cetak per transaksi (white-label). Hanya tampil saat print. --}}
    @if ($receiptData && $receiptBranding)
        <div
            id="sales-receipt-print"
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
                <div>{{ __('No.') }} {{ $receiptData['invoice_number'] }}</div>
                <div>{{ $receiptData['completed_at'] }}</div>
                @if (!empty($receiptData['buyer_name']) || !empty($receiptData['buyer_phone']))
                    <div style="margin-top: 4px; font-size: 10px;">
                        @if (!empty($receiptData['buyer_name'])){{ __('Pembeli') }}: {{ $receiptData['buyer_name'] }}@endif
                        @if (!empty($receiptData['buyer_name']) && !empty($receiptData['buyer_phone'])) · @endif
                        @if (!empty($receiptData['buyer_phone'])){{ $receiptData['buyer_phone'] }}@endif
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
                    @foreach ($receiptData['items'] ?? [] as $item)
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
                <div style="display: flex; justify-content: space-between;"><span>{{ __('Subtotal') }}</span><span>Rp {{ number_format(($receiptData['subtotal'] ?? 0) / 100, 0, ',', '.') }}</span></div>
                @if (($receiptData['discount_amount'] ?? 0) > 0)
                    <div style="display: flex; justify-content: space-between;"><span>{{ __('Diskon') }}</span><span>- Rp {{ number_format(($receiptData['discount_amount'] ?? 0) / 100, 0, ',', '.') }}</span></div>
                @endif
                <div style="display: flex; justify-content: space-between; font-weight: 700;"><span>{{ __('Total') }}</span><span>Rp {{ number_format(($receiptData['total_amount'] ?? 0) / 100, 0, ',', '.') }}</span></div>
                <div style="display: flex; justify-content: space-between;"><span>{{ __('Bayar') }} ({{ $receiptData['payment_method'] ?? '' }})</span><span>Rp {{ number_format(($receiptData['amount_paid'] ?? 0) / 100, 0, ',', '.') }}</span></div>
                @if (($receiptData['change_amount'] ?? 0) > 0)
                    <div style="display: flex; justify-content: space-between;"><span>{{ __('Kembalian') }}</span><span>Rp {{ number_format(($receiptData['change_amount'] ?? 0) / 100, 0, ',', '.') }}</span></div>
                @endif
            </div>
            <div style="text-align: center; margin-top: 8px; font-size: 10px; color: #666;">{{ __('Terima kasih') }}</div>
        </div>
    @endif

    {{-- Tabel untuk cetak (sesuai filter). --}}
    @if (is_object($printTableTransactions) ? $printTableTransactions->isNotEmpty() : count($printTableTransactions) > 0)
        <div
            id="sales-table-print"
            style="visibility: hidden; position: fixed; left: -9999px; top: 0; width: 100%; max-width: 210mm; padding: 12px; font-size: 12px; background: #fff; color: #000; box-sizing: border-box;"
        >
            <h2 style="margin: 0 0 8px 0; font-size: 18px;">{{ __('Laporan Penjualan - Detail Transaksi') }}</h2>
            <p style="margin: 0 0 12px 0; font-size: 11px; color: #666;">
                {{ __('Periode') }}: {{ $dateFrom }} s/d {{ $dateTo }}
            </p>
            <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                <thead>
                    <tr style="border-bottom: 2px solid #333;">
                        <th style="text-align: left; padding: 6px 8px;">{{ __('Invoice') }}</th>
                        <th style="text-align: left; padding: 6px 8px;">{{ __('Tanggal') }}</th>
                        <th style="text-align: left; padding: 6px 8px;">{{ __('Pembeli') }}</th>
                        <th style="text-align: left; padding: 6px 8px;">{{ __('No. Telp') }}</th>
                        <th style="text-align: left; padding: 6px 8px;">{{ __('Kasir') }}</th>
                        <th style="text-align: right; padding: 6px 8px;">{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($printTableTransactions as $tx)
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 6px 8px; font-family: monospace;">{{ $tx->invoice_number }}</td>
                            <td style="padding: 6px 8px;">{{ $tx->completed_at?->format('d M Y H:i') }}</td>
                            <td style="padding: 6px 8px;">{{ $tx->buyer_name ?? '–' }}</td>
                            <td style="padding: 6px 8px;">{{ $tx->buyer_phone ?? '–' }}</td>
                            <td style="padding: 6px 8px;">{{ $tx->cashier->name ?? '-' }}</td>
                            <td style="padding: 6px 8px; text-align: right; font-weight: 600;">Rp {{ number_format($tx->total_amount / 100, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p style="margin-top: 12px; font-weight: 700; font-size: 12px;">
                {{ __('Total (sesuai filter)') }}: Rp {{ number_format(collect($printTableTransactions)->sum('total_amount') / 100, 0, ',', '.') }}
            </p>
        </div>
    @endif

    <style media="print">
        @page { size: auto; margin: 5mm; }
        body * { visibility: hidden; }
        #sales-receipt-print.active-print, #sales-receipt-print.active-print * { visibility: visible !important; }
        #sales-receipt-print.active-print {
            position: absolute !important; left: 50% !important; top: 0 !important; transform: translateX(-50%) !important;
            width: 100% !important; max-width: 80mm !important; min-width: 0 !important;
            box-shadow: none !important; background: #fff !important; box-sizing: border-box !important;
        }
        #sales-table-print.active-print, #sales-table-print.active-print * { visibility: visible !important; }
        #sales-table-print.active-print {
            position: absolute !important; left: 0 !important; top: 0 !important; width: 100% !important;
            box-shadow: none !important; background: #fff !important; box-sizing: border-box !important;
        }
    </style>
</div>

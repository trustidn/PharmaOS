<?php

namespace App\Livewire\Report;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Services\BrandingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class SalesReport extends Component
{
    use WithPagination;

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $period = 'today';

    public ?int $receiptTransactionId = null;

    /** @var \Illuminate\Database\Eloquent\Collection<int, Transaction>|array */
    public $printTableTransactions = [];

    public function mount(): void
    {
        $this->applyPeriod();
    }

    public function updatedPeriod(): void
    {
        $this->applyPeriod();
    }

    private function applyPeriod(): void
    {
        $this->dateFrom = match ($this->period) {
            'today' => today()->format('Y-m-d'),
            'week' => now()->startOfWeek()->format('Y-m-d'),
            'month' => now()->startOfMonth()->format('Y-m-d'),
            'year' => now()->startOfYear()->format('Y-m-d'),
            default => $this->dateFrom,
        };
        $this->dateTo = today()->format('Y-m-d');
    }

    private function baseQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Transaction::query()
            ->where('type', TransactionType::Sale)
            ->where('status', TransactionStatus::Completed)
            ->when($this->dateFrom, fn ($q) => $q->whereDate('completed_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('completed_at', '<=', $this->dateTo));
    }

    public function openReceiptPrint(int $transactionId): void
    {
        $this->receiptTransactionId = $transactionId;
        $this->dispatch('print-receipt');
    }

    public function openTablePrint(): void
    {
        $this->printTableTransactions = $this->baseQuery()
            ->with('cashier')
            ->latest()
            ->limit(500)
            ->get();
        $this->dispatch('print-sales-table');
    }

    public function render()
    {
        $query = $this->baseQuery();

        $summary = [
            'total_transactions' => (clone $query)->count(),
            'total_revenue' => (clone $query)->sum('total_amount'),
            'total_discount' => (clone $query)->sum('discount_amount'),
            'average_transaction' => (clone $query)->avg('total_amount') ?? 0,
        ];

        $dailySales = (clone $query)
            ->select(
                DB::raw('DATE(completed_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total'),
            )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        $transactions = (clone $query)->with('cashier')->latest()->paginate(20);

        $receiptData = null;
        $receiptBranding = null;
        if ($this->receiptTransactionId) {
            $tx = Transaction::with('items')->find($this->receiptTransactionId);
            if ($tx && $tx->type === TransactionType::Sale && $tx->status === TransactionStatus::Completed) {
                $receiptData = [
                    'invoice_number' => $tx->invoice_number,
                    'completed_at' => $tx->completed_at->format('d/m/Y H:i'),
                    'buyer_name' => $tx->buyer_name,
                    'buyer_phone' => $tx->buyer_phone,
                    'items' => $tx->items->map(fn ($item) => [
                        'name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'unit_name' => $item->unit_name ?? 'pcs',
                        'unit_price' => $item->unit_price,
                        'discount' => $item->discount_amount,
                        'subtotal' => $item->subtotal,
                    ])->values()->all(),
                    'subtotal' => $tx->subtotal,
                    'discount_amount' => $tx->discount_amount,
                    'total_amount' => $tx->total_amount,
                    'payment_method' => $tx->payment_method->label(),
                    'amount_paid' => $tx->amount_paid,
                    'change_amount' => $tx->change_amount,
                ];
                $branding = app(BrandingService::class)->getBranding();
                $receiptBranding = [
                    'name' => $branding['name'],
                    'logo_url' => $branding['logo_path'] ? Storage::url($branding['logo_path']) : null,
                    'primary_color' => $branding['primary_color'],
                    'address' => $branding['address'] ?? null,
                    'phone' => $branding['phone'] ?? null,
                    'website' => $branding['website'] ?? null,
                ];
            }
        }

        return view('livewire.report.sales-report', [
            'summary' => $summary,
            'dailySales' => $dailySales,
            'transactions' => $transactions,
            'receiptData' => $receiptData,
            'receiptBranding' => $receiptBranding,
        ]);
    }
}

<?php

namespace App\Livewire\Dashboard;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\PlanLimitService;
use App\Services\TenantContext;
use Livewire\Component;

class Overview extends Component
{
    public function render()
    {
        $context = app(TenantContext::class);
        $isTenant = $context->hasTenant();
        $user = auth()->user();

        $data = [
            'userName' => $user->name,
            'userRoleLabel' => $user->role->label(),
            'greeting' => $this->greeting(),
        ];

        if ($isTenant) {
            $subscription = app(PlanLimitService::class)->getSubscription();
            $data['subscription'] = $subscription;
        }

        if ($isTenant) {
            $data['todayRevenue'] = Transaction::where('type', TransactionType::Sale)
                ->where('status', TransactionStatus::Completed)
                ->whereDate('completed_at', today())
                ->sum('total_amount');

            $data['todayTransactions'] = Transaction::where('type', TransactionType::Sale)
                ->where('status', TransactionStatus::Completed)
                ->whereDate('completed_at', today())
                ->count();

            $data['monthRevenue'] = Transaction::where('type', TransactionType::Sale)
                ->where('status', TransactionStatus::Completed)
                ->whereMonth('completed_at', now()->month)
                ->whereYear('completed_at', now()->year)
                ->sum('total_amount');

            $data['totalProducts'] = Product::where('is_active', true)->count();

            $data['lowStockProducts'] = Product::where('is_active', true)
                ->withSum([
                    'batches as available_stock' => function ($q) {
                        $q->where('is_active', true)
                            ->where('expired_at', '>', now())
                            ->where('quantity_remaining', '>', 0);
                    },
                ], 'quantity_remaining')
                ->get()
                ->filter(fn ($p) => ($p->available_stock ?? 0) <= $p->min_stock)
                ->take(5);

            $data['nearExpiryBatches'] = Batch::with('product')
                ->where('is_active', true)
                ->where('quantity_remaining', '>', 0)
                ->where('expired_at', '>', now())
                ->where('expired_at', '<=', now()->addDays(90))
                ->orderBy('expired_at')
                ->limit(5)
                ->get();

            $data['recentTransactions'] = Transaction::with('cashier')
                ->where('status', TransactionStatus::Completed)
                ->latest()
                ->limit(5)
                ->get();
        }

        return view('livewire.dashboard.overview', [
            'data' => $data,
            'isTenant' => $isTenant,
        ]);
    }

    private function greeting(): string
    {
        $hour = (int) now()->format('G');
        if ($hour < 12) {
            return __('Selamat pagi');
        }
        if ($hour < 15) {
            return __('Selamat siang');
        }
        if ($hour < 19) {
            return __('Selamat sore');
        }

        return __('Selamat malam');
    }
}

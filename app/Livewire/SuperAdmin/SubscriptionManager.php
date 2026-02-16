<?php

namespace App\Livewire\SuperAdmin;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\Tenant;
use Livewire\Component;

class SubscriptionManager extends Component
{
    public Tenant $tenant;

    public string $plan = 'basic';

    public string $status = 'active';

    public function mount(int $tenantId): void
    {
        $this->authorize('viewAny', Tenant::class);

        $this->tenant = Tenant::with('activeSubscription', 'subscriptions')->findOrFail($tenantId);

        if ($this->tenant->activeSubscription) {
            $this->plan = $this->tenant->activeSubscription->plan->value;
            $this->status = $this->tenant->activeSubscription->status->value;
        }
    }

    public function updateSubscription(): void
    {
        $this->authorize('viewAny', Tenant::class);

        $subscriptionPlan = SubscriptionPlan::from($this->plan);
        $subscriptionStatus = SubscriptionStatus::from($this->status);

        $currentSubscription = $this->tenant->activeSubscription;

        if ($currentSubscription) {
            $currentSubscription->update([
                'plan' => $subscriptionPlan,
                'status' => $subscriptionStatus,
                'max_products' => $subscriptionPlan->maxProducts(),
                'max_users' => $subscriptionPlan->maxUsers(),
                'max_transactions_per_month' => $subscriptionPlan->maxTransactionsPerMonth(),
                'price' => $subscriptionPlan->monthlyPrice(),
                'cancelled_at' => $subscriptionStatus === SubscriptionStatus::Cancelled ? now() : null,
            ]);
        } else {
            Subscription::create([
                'tenant_id' => $this->tenant->id,
                'plan' => $subscriptionPlan,
                'status' => $subscriptionStatus,
                'max_products' => $subscriptionPlan->maxProducts(),
                'max_users' => $subscriptionPlan->maxUsers(),
                'max_transactions_per_month' => $subscriptionPlan->maxTransactionsPerMonth(),
                'price' => $subscriptionPlan->monthlyPrice(),
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
            ]);
        }

        $this->tenant->refresh();
        session()->flash('success', 'Langganan berhasil diperbarui.');
    }

    public function render()
    {
        return view('livewire.super-admin.subscription-manager', [
            'plans' => SubscriptionPlan::cases(),
            'statuses' => SubscriptionStatus::cases(),
        ]);
    }
}

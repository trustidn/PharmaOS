<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\Tenant;

/**
 * Service to check and enforce subscription plan limits.
 */
class PlanLimitService
{
    public function __construct(
        private TenantContext $tenantContext,
    ) {}

    /**
     * Get the active subscription for the current tenant.
     */
    public function getSubscription(): ?Subscription
    {
        $tenant = $this->tenantContext->getTenant();

        if (! $tenant) {
            return null;
        }

        return $tenant->activeSubscription;
    }

    /**
     * Check if the current plan has a specific feature.
     */
    public function hasFeature(string $feature): bool
    {
        $subscription = $this->getSubscription();

        if (! $subscription) {
            return false;
        }

        return $subscription->plan->hasFeature($feature);
    }

    /**
     * Check if the tenant can add more products.
     */
    public function canAddProduct(): bool
    {
        $subscription = $this->getSubscription();

        if (! $subscription) {
            return false;
        }

        $tenant = $this->tenantContext->getTenant();
        $currentCount = $tenant->products()->count();

        return $currentCount < $subscription->max_products;
    }

    /**
     * Check if the tenant can add more users.
     */
    public function canAddUser(): bool
    {
        $subscription = $this->getSubscription();

        if (! $subscription) {
            return false;
        }

        $tenant = $this->tenantContext->getTenant();
        $currentCount = $tenant->users()->count();

        return $currentCount < $subscription->max_users;
    }

    /**
     * Check if the tenant can create more transactions this month.
     */
    public function canCreateTransaction(): bool
    {
        $subscription = $this->getSubscription();

        if (! $subscription) {
            return false;
        }

        $tenant = $this->tenantContext->getTenant();
        $currentMonthCount = $tenant->transactions()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return $currentMonthCount < $subscription->max_transactions_per_month;
    }

    /**
     * Get remaining product slots.
     */
    public function remainingProducts(): int
    {
        $subscription = $this->getSubscription();

        if (! $subscription) {
            return 0;
        }

        $tenant = $this->tenantContext->getTenant();

        return max(0, $subscription->max_products - $tenant->products()->count());
    }

    /**
     * Get remaining transaction slots for this month.
     */
    public function remainingTransactions(): int
    {
        $subscription = $this->getSubscription();

        if (! $subscription) {
            return 0;
        }

        $tenant = $this->tenantContext->getTenant();
        $currentMonthCount = $tenant->transactions()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return max(0, $subscription->max_transactions_per_month - $currentMonthCount);
    }
}

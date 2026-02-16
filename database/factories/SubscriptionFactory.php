<?php

namespace Database\Factories;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plan = SubscriptionPlan::Basic;

        return [
            'tenant_id' => Tenant::factory(),
            'plan' => $plan,
            'status' => SubscriptionStatus::Active,
            'max_products' => $plan->maxProducts(),
            'max_users' => $plan->maxUsers(),
            'max_transactions_per_month' => $plan->maxTransactionsPerMonth(),
            'price' => $plan->monthlyPrice(),
            'trial_ends_at' => null,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'cancelled_at' => null,
        ];
    }

    public function plan(SubscriptionPlan $plan): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => $plan,
            'max_products' => $plan->maxProducts(),
            'max_users' => $plan->maxUsers(),
            'max_transactions_per_month' => $plan->maxTransactionsPerMonth(),
            'price' => $plan->monthlyPrice(),
        ]);
    }

    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Trial,
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }

    public function pastDue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::PastDue,
        ]);
    }
}

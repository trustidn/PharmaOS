<?php

namespace App\Livewire\SuperAdmin;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Component;

class TenantForm extends Component
{
    public ?Tenant $tenant = null;

    public string $name = '';

    public string $owner_name = '';

    public string $email = '';

    public string $phone = '';

    public string $address = '';

    public string $license_number = '';

    public string $plan = 'basic';

    // Owner account fields (only for creation)
    public string $owner_email = '';

    public string $owner_password = '';

    public function mount(?int $tenantId = null): void
    {
        $this->authorize('viewAny', Tenant::class);

        if ($tenantId) {
            $this->tenant = Tenant::findOrFail($tenantId);
            $this->fill($this->tenant->only([
                'name', 'owner_name', 'email', 'phone', 'address', 'license_number',
            ]));
            $this->plan = $this->tenant->activeSubscription?->plan->value ?? 'basic';
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'plan' => ['required', 'in:basic,pro,enterprise'],
        ];

        if (! $this->tenant) {
            $rules['owner_email'] = ['required', 'email', 'unique:users,email'];
            $rules['owner_password'] = ['required', 'string', 'min:8'];
        }

        return $rules;
    }

    public function save(): void
    {
        $this->authorize('viewAny', Tenant::class);
        $this->validate();

        if ($this->tenant) {
            $this->tenant->update([
                'name' => $this->name,
                'owner_name' => $this->owner_name,
                'email' => $this->email,
                'phone' => $this->phone ?: null,
                'address' => $this->address ?: null,
                'license_number' => $this->license_number ?: null,
            ]);

            session()->flash('success', 'Tenant berhasil diperbarui.');
        } else {
            $tenant = Tenant::create([
                'name' => $this->name,
                'slug' => Str::slug($this->name).'-'.Str::random(4),
                'owner_name' => $this->owner_name,
                'email' => $this->email,
                'phone' => $this->phone ?: null,
                'address' => $this->address ?: null,
                'license_number' => $this->license_number ?: null,
            ]);

            $subscriptionPlan = SubscriptionPlan::from($this->plan);

            Subscription::create([
                'tenant_id' => $tenant->id,
                'plan' => $subscriptionPlan,
                'status' => SubscriptionStatus::Active,
                'max_products' => $subscriptionPlan->maxProducts(),
                'max_users' => $subscriptionPlan->maxUsers(),
                'max_transactions_per_month' => $subscriptionPlan->maxTransactionsPerMonth(),
                'price' => $subscriptionPlan->monthlyPrice(),
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
            ]);

            User::create([
                'tenant_id' => $tenant->id,
                'name' => $this->owner_name,
                'role' => UserRole::Owner,
                'email' => $this->owner_email,
                'password' => $this->owner_password,
                'email_verified_at' => now(),
            ]);

            session()->flash('success', 'Tenant berhasil dibuat dengan akun owner.');
        }

        $this->redirect(route('admin.tenants'), navigate: true);
    }

    public function render()
    {
        return view('livewire.super-admin.tenant-form', [
            'plans' => SubscriptionPlan::cases(),
        ]);
    }
}

<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Component;

class SystemDashboard extends Component
{
    public function render()
    {
        $this->authorize('viewAny', Tenant::class);

        return view('livewire.super-admin.system-dashboard', [
            'totalTenants' => Tenant::count(),
            'activeTenants' => Tenant::where('is_active', true)->count(),
            'totalUsers' => User::whereNotNull('tenant_id')->count(),
            'totalTransactionsToday' => Transaction::withoutGlobalScopes()->whereDate('created_at', today())->count(),
        ]);
    }
}

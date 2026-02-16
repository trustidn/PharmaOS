<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionActive
{
    public function __construct(
        private TenantContext $tenantContext,
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Super Admin bypasses subscription check
        if (! $user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $tenant = $this->tenantContext->getTenant();

        if (! $tenant) {
            abort(403, 'Tenant context tidak ditemukan.');
        }

        $subscription = $tenant->activeSubscription;

        if (! $subscription || ! $subscription->isUsable()) {
            return redirect()->route('subscription.expired');
        }

        if ($subscription->isTrialExpired()) {
            return redirect()->route('subscription.expired');
        }

        return $next($request);
    }
}

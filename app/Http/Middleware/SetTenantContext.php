<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
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

        if ($user && $user->tenant_id) {
            $tenant = Tenant::find($user->tenant_id);

            if (! $tenant || ! $tenant->is_active) {
                abort(403, 'Tenant tidak aktif atau tidak ditemukan.');
            }

            $this->tenantContext->setTenant($tenant);
        }

        return $next($request);
    }
}

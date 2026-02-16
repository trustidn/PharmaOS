<?php

namespace App\Http\Middleware;

use App\Services\PlanLimitService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureLimit
{
    public function __construct(
        private PlanLimitService $planLimitService,
    ) {}

    /**
     * Handle an incoming request.
     *
     * Usage in routes: ->middleware('check.feature:supplier_management')
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        // Super Admin bypasses feature checks
        if (! $user || $user->isSuperAdmin()) {
            return $next($request);
        }

        if (! $this->planLimitService->hasFeature($feature)) {
            abort(403, 'Fitur ini tidak tersedia dalam paket langganan Anda.');
        }

        return $next($request);
    }
}

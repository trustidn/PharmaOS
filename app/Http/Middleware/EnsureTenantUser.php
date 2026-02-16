<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantUser
{
    /**
     * Ensure the authenticated user is a tenant user (not Super Admin).
     * Protects tenant-only routes from being accessed by Super Admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isSuperAdmin()) {
            abort(403, 'Halaman ini hanya tersedia untuk pengguna tenant.');
        }

        return $next($request);
    }
}

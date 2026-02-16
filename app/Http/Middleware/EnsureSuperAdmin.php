<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Ensure the authenticated user is a Super Admin.
     * Protects admin-only routes from being accessed by tenant users.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isSuperAdmin()) {
            abort(403, 'Halaman ini hanya tersedia untuk Super Admin.');
        }

        return $next($request);
    }
}

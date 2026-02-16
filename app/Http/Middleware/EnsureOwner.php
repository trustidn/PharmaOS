<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwner
{
    /**
     * Ensure the authenticated user has Owner role (tenant only).
     * Used to restrict sensitive features like reports to apotek owner.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isOwner()) {
            abort(403, 'Halaman ini hanya dapat diakses oleh Pemilik Apotek.');
        }

        return $next($request);
    }
}

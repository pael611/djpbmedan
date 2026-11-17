<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dashboard Master (dashboard.index) hanya untuk ADMIN & SKKI.
 * Role lain di-redirect ke dashboard bidang masing-masing.
 */
class CheckRoleDashboard
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->isPrivileged()) {
            return redirect()
                ->route('dashboard.bidang')
                ->with('error', 'Anda tidak memiliki akses ke Dashboard Master.');
        }

        return $next($request);
    }
}

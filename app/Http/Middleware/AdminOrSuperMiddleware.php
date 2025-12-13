<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrSuperMiddleware
{
    /**
     * Handle an incoming request.
     * Super Admin & Admin Cabang bisa akses
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        if (!$user || (!$user->isSuperAdmin() && !$user->isAdminCabang())) {
            abort(403, 'Akses ditolak. Anda tidak memiliki hak akses sebagai Administrator');
        }

        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CabangAccessMiddleware
{
    /**
     * Handle an incoming request.
     * Middleware ini memastikan user hanya bisa akses data cabang yang sesuai
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Super admin bisa akses semua
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Admin cabang dan kasir harus punya cabang_id
        if (!$user->cabang_id) {
            abort(403, 'User belum ditugaskan ke cabang manapun');
        }

        // Inject cabang_id ke request untuk filter otomatis
        $request->merge(['user_cabang_id' => $user->cabang_id]);

        return $next($request);
    }
}
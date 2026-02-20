<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek Login & Role
        if (Auth::check() && Auth::user()->role !== 'admin') {
            // Jika Manag/Direktur mencoba akses, alihkan ke Project Aplikasi
            if (in_array(Auth::user()->role, ['kepala_ruang', 'direktur'])) {
                return redirect()->route('apps.index'); 
            }
            // User lain (misal staff biasa) kembalikan ke tracking publik
            return redirect()->route('public.tracking');
        }

        return $next($request);
    }
}
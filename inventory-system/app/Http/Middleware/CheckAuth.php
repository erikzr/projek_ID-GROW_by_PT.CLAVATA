<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user sudah login berdasarkan session
        if (!session('user_logged_in') || !session('user_token')) {
            return redirect()->route('login')
                ->with('error', 'Token tidak ditemukan, silakan login ulang');
        }

        // Cek apakah token masih valid (optional - bisa ditambahkan expiry check)
        $tokenExpiry = session('token_expiry');
        if ($tokenExpiry && now()->timestamp > $tokenExpiry) {
            // Token expired, hapus session
            session()->forget(['user_logged_in', 'user_token', 'token_expiry', 'user_data']);
            return redirect()->route('login')
                ->with('error', 'Session expired, silakan login ulang');
        }

        return $next($request);
    }
}
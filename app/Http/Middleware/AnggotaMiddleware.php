<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AnggotaMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();

        // Jika user adalah admin, redirect ke Filament panel
        if ($user->user_group === 'admin') {
            return redirect('/admin')->with('info', 'Anda adalah admin, silakan gunakan panel admin.');
        }

        // Jika bukan anggota dan bukan admin
        if ($user->user_group !== 'anggota') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect('/login')->with('error', 'Role tidak valid.');
        }

        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceSessionIdleTimeout
{
    private const LAST_ACTIVITY_KEY = 'last_activity_at';

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        if ($request->routeIs('login.post', 'logout')) {
            return $next($request);
        }

        $session = $request->session();
        $timeoutMinutes = (int) config('session.lifetime', 45);
        $lifetimeInSeconds = $timeoutMinutes * 60;
        $lastActivityAt = (int) $session->get(self::LAST_ACTIVITY_KEY, now()->timestamp);

        if ((now()->timestamp - $lastActivityAt) >= $lifetimeInSeconds) {
            Auth::logout();
            $session->invalidate();
            $session->regenerateToken();

            if ($request->expectsJson() || $request->ajax() || $request->routeIs('session.check', 'auth.session-user')) {
                return response()->json([
                    'authenticated' => false,
                    'message' => "Sesi Anda telah berakhir karena tidak ada aktivitas selama {$timeoutMinutes} menit.",
                    'redirect' => route('login'),
                ], 401);
            }

            return redirect('/login')->with('error', "Sesi Anda telah berakhir karena tidak ada aktivitas selama {$timeoutMinutes} menit.");
        }

        $response = $next($request);

        // Perbarui last_activity_at kecuali request polling.
        // Polling JS tidak boleh mereset timer idle.
        if (! $request->routeIs('session.check', 'auth.session-user')) {
            $request->session()->put(self::LAST_ACTIVITY_KEY, now()->timestamp);
        }

        // Paksa browser tidak menyimpan halaman di cache/BFCache
        // sehingga saat dibuka setelah sleep selalu request ke server
        if (! $request->expectsJson() && ! $request->ajax()) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}

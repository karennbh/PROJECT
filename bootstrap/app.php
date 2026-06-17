<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'karyawan' => \App\Http\Middleware\KaryawanMiddleware::class,
            'idle.timeout' => \App\Http\Middleware\EnforceSessionIdleTimeout::class,
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\EnforceSessionIdleTimeout::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException|HttpException $exception, Request $request) {
            $isPageExpired = $exception instanceof TokenMismatchException
                || ($exception instanceof HttpException && $exception->getStatusCode() === 419);

            if (! $isPageExpired) {
                return null;
            }

            $target = auth()->check()
                ? (auth()->user()?->user_group === 'admin' ? '/admin' : '/dashboard')
                : '/login';

            // Gunakan 303 See Other agar browser selalu menggunakan GET setelah redirect,
            // termasuk saat request aslinya adalah POST (Livewire, form submit, dsb.)
            return response(null, 303, ['Location' => $target])
                ->withCookie(cookie()->forget('XSRF-TOKEN'));
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $exception, Request $request) {
            // Jika ada request non-GET yang menghantam route yang hanya menerima GET
            // (misalnya Livewire/fetch yang ter-redirect), kembalikan ke halaman yang sesuai.
            $target = auth()->check()
                ? (auth()->user()?->user_group === 'admin' ? '/admin' : '/dashboard')
                : '/login';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Redirecting...', 'redirect' => $target], 302);
            }

            return response(null, 303, ['Location' => $target]);
        });
    })->create();

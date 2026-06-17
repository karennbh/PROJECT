<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        // PENTING: Jika user sudah login, cek apakah session masih valid
        if (Auth::check() && ! $request->boolean('tab_reauth')) {
            $user = Auth::user();
            
            // Hapus intended URL untuk mencegah redirect ke halaman lama
            session()->forget('url.intended');
            
            if ($user->user_group === 'admin') {
                return redirect('/admin');
            }
            
            if ($user->user_group === 'anggota') {
                return redirect('/dashboard');
            }

            // Jika role tidak dikenali, logout
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect('/login')->with('error', 'Role tidak valid.');
        }

        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|min:6',
            'next' => 'nullable|string',
        ]);

        $credentials = [
            'username' => $validated['username'],
            'password' => $validated['password'],
        ];

        // GUNAKAN FALSE agar session TIDAK persistent (expire saat browser ditutup)
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $request->session()->put('last_activity_at', now()->timestamp);
            
            // Hapus intended URL
            session()->forget('url.intended');
            
            $user = Auth::user();
            $next = $request->input('next');
            $redirectTo = null;

            if (is_string($next) && str_starts_with($next, '/') && ! str_starts_with($next, '//')) {
                $redirectTo = $next;
            } elseif ($user->user_group === 'admin') {
                $redirectTo = '/admin';
            } elseif ($user->user_group === 'anggota') {
                $redirectTo = '/dashboard';
            }

            if ($redirectTo) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'redirect' => $redirectTo,
                        'user_id' => $user->getKey(),
                        'user_group' => $user->user_group,
                    ]);
                }

                return redirect($redirectTo);
            }
            
            // Jika user_group tidak dikenali
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            
            $message = 'Role pengguna tidak valid.';

            if ($request->expectsJson()) {
                    return response()->json([
                        'message' => $message,
                        'errors' => ['username' => [$message]],
                    ], 422);
                }

                return back()->withErrors([
                    'username' => $message,
                ]);
            }

        $message = 'Username atau password salah.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'errors' => ['username' => [$message]],
            ], 422);
        }

        return back()->withErrors([
            'username' => $message,
        ])->withInput($request->only('username'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        // PENTING: Hapus semua session data
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Hapus semua session yang tersisa
        session()->flush();
        
        // SELALU REDIRECT KE LOGIN SETELAH LOGOUT
        return redirect('/login')->with('success', 'Berhasil logout.');
    }

    public function sessionUser(Request $request)
    {
        $user = Auth::user();

        return response()
            ->json([
                'authenticated' => $user !== null,
                'user_id' => $user?->getKey(),
                'user_group' => $user?->user_group,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private')
            ->header('Pragma', 'no-cache');
    }

    public function ubahpassword()
    {
        return view('auth.ubahpassword');
    }

    public function prosesubahpassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:5|confirmed',
        ]);
        
        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('anggota.dashboard')
            ->with('success', 'Password berhasil diperbarui!');
    }
}

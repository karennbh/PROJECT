<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\AnggotaMiddleware;
use App\Http\Controllers\KetersediaanController;
use App\Http\Controllers\PeminjamanBarangController;
use App\Http\Controllers\PemakaianBHPController;
use App\Http\Controllers\PembelianBarangController;
use App\Http\Controllers\RiwayatAktivitasController;
use App\Models\BarangKantor;

Route::get('/public/barang-kantor/{kodeBarang}', function (string $kodeBarang) {
    $barang = BarangKantor::query()
        ->with(['kategoriAset', 'penyusutans'])
        ->where('barcode', $kodeBarang)
        ->orWhere('kode_barang', $kodeBarang)
        ->orWhere('nama_barang', $kodeBarang)
        ->firstOrFail();

    $penyusutan = $barang->kategori_barang === 'aset'
        ? $barang->penyusutans()->latest('id_penyusutan')->first()
        : null;

    return view('public.barang-detail', [
        'barang' => $barang,
        'penyusutan' => $penyusutan,
    ]);
})->name('barang.public-detail');

Route::get('/barang-kantor/detail/{kodeBarang}', function (string $kodeBarang) {
    $barang = BarangKantor::query()
        ->where('barcode', $kodeBarang)
        ->orWhere('kode_barang', $kodeBarang)
        ->orWhere('nama_barang', $kodeBarang)
        ->firstOrFail();

    return redirect()->route('barang.public-detail', [
        'kodeBarang' => $barang->kode_barang,
    ]);
})->name('barang.detail');

Route::get('/scan/barang/{barcode}', function (string $barcode) {
    $barang = BarangKantor::query()
        ->where('barcode', $barcode)
        ->orWhere('kode_barang', $barcode)
        ->firstOrFail();

    return redirect()->route('barang.public-detail', [
        'kodeBarang' => $barang->kode_barang,
    ]);
})->name('scan.barang');


// Route default - SELALU REDIRECT KE LOGIN JIKA BELUM AUTH
Route::any('/', function () {
    // Jika belum login, langsung ke halaman login
    if (!auth()->check()) {
        return redirect('/login');
    }

    // Hapus intended URL untuk mencegah redirect ke halaman lama
    session()->forget('url.intended');

    $user = auth()->user();
    
    // Redirect berdasarkan role
    if ($user->user_group === 'admin') {
        return redirect('/admin');
    }
    
    if ($user->user_group === 'anggota') {
        return redirect('/dashboard');
    }
    
    // Role tidak dikenali - logout dan redirect ke login
    auth()->logout();
    session()->invalidate();
    session()->regenerateToken();
    
    return redirect('/login')->with('error', 'Role tidak valid.');
});

Route::match(['post', 'put', 'patch', 'delete', 'options'], '/admin', function () {
    return redirect('/admin');
});

Route::match(['post', 'put', 'patch', 'delete', 'options'], '/admin/barang-kantors', function () {
    return redirect('/admin/barang-kantors');
});

// Auth Routes - Login untuk semua user
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/auth/session-user', [AuthController::class, 'sessionUser'])->name('auth.session-user');

// Cek status sesi — digunakan JS timeout untuk deteksi idle server-side
Route::get('/session-check', function () {
    if (! auth()->check()) {
        return response()->json(['authenticated' => false], 401);
    }
    return response()->json(['authenticated' => true]);
})->middleware('auth')->name('session.check');

// Logout Route - Accessible untuk semua authenticated user
Route::any('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Route Anggota (Laravel Dashboard)
Route::middleware(['auth', AnggotaMiddleware::class])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('anggota.dashboard');

    // --- TAMBAHKAN INI ---
    Route::get('/ketersediaan', [KetersediaanController::class, 'index'])
        ->name('ketersediaan.index');
    // Peminjaman Barang Kantor
    Route::get('/peminjaman', [PeminjamanBarangController::class, 'index'])->name('peminjaman.index');
    Route::post('/peminjaman', [PeminjamanBarangController::class, 'store'])->name('peminjaman.store');
    Route::get('/peminjaman/riwayat', [PeminjamanBarangController::class, 'riwayatSemua'])->name('peminjaman.riwayat');
    Route::patch('/peminjaman/kembalikan/{id}', [PeminjamanBarangController::class, 'kembalikan'])->name('peminjaman.kembalikan');
    // Pemakaian BHP
    Route::get('/pemakaian', [PemakaianBHPController::class, 'index'])->name('pemakaian.index');
    Route::post('/pemakaian', [PemakaianBHPController::class, 'store'])->name('pemakaian.store');
    // Pengajuan Pembelian Barang
    Route::get('/pembelian', [PembelianBarangController::class, 'index'])->name('pembelian.index');
    Route::post('/pembelian', [PembelianBarangController::class, 'store'])->name('pembelian.store');
    Route::get('/pembelian/riwayat', [PembelianBarangController::class, 'riwayatSemua'])->name('pembelian.riwayat');
    // Riwayat Aktivitas
    Route::get('/riwayat', [RiwayatAktivitasController::class, 'index'])->name('riwayat.index');
    Route::get('/pemakaian/riwayat', [PemakaianBHPController::class, 'riwayatSemua'])->name('pemakaian.riwayat');
    
    Route::get('/ubah-password', [AuthController::class, 'ubahpassword'])
        ->name('anggota.ubah-password');
        
    Route::post('/ubah-password', [AuthController::class, 'prosesubahpassword'])
        ->name('anggota.proses-ubah-password');

    // Tambahkan route anggota lainnya di sini
    // Route::get('/profil', ...)->name('anggota.profil');
});

// Filament akan handle route /admin secara otomatis dengan middleware admin
// User akan diarahkan ke /login jika belum auth atau bukan admin

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'id_user';

    protected $fillable = [
        'name',
        'username',
        'password',
        'user_group', // admin atau anggota
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function pengajuanPembelianBarangs()
    {
        return $this->hasMany(PengajuanPembelianBarang::class, 'user_id', 'id_user');
    }

    public function peminjamanBarangs()
    {
        return $this->hasMany(PeminjamanBarang::class, 'user_id', 'id_user');
    }

    public function pemakaianBHPs()
    {
        return $this->hasMany(PemakaianBHP::class, 'user_id', 'id_user');
    }

    /**
     * METHOD INI UNTUK CEK AKSES KE FILAMENT PANEL
     * Dipanggil otomatis oleh Filament setiap kali akses panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Hanya admin yang boleh akses panel 'admin'
        if ($panel->getId() === 'admin') {
            return $this->user_group === 'admin';
        }

        return false;
    }
}

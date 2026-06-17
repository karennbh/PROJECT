<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // 1. Ubah enum — tambah nilai baru dulu sebelum hapus yang lama
        DB::statement("ALTER TABLE barang_kantors MODIFY COLUMN status_pinjam ENUM('Tersedia','Dipinjam','Sedang Dipinjam','Telah Didistribusikan','Tidak untuk Dipinjamkan') NULL DEFAULT 'Tersedia'");

        // 2. Migrasi data existing: 'Dipinjam' → 'Sedang Dipinjam'
        DB::statement("UPDATE barang_kantors SET status_pinjam = 'Sedang Dipinjam' WHERE status_pinjam = 'Dipinjam'");

        // 3. Hapus nilai lama dari enum
        DB::statement("ALTER TABLE barang_kantors MODIFY COLUMN status_pinjam ENUM('Tersedia','Sedang Dipinjam','Telah Didistribusikan','Tidak untuk Dipinjamkan') NULL DEFAULT 'Tersedia'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE barang_kantors MODIFY COLUMN status_pinjam ENUM('Tersedia','Dipinjam','Sedang Dipinjam','Telah Didistribusikan','Tidak untuk Dipinjamkan') NULL DEFAULT 'Tersedia'");
        DB::statement("UPDATE barang_kantors SET status_pinjam = 'Dipinjam' WHERE status_pinjam = 'Sedang Dipinjam'");
        DB::statement("ALTER TABLE barang_kantors MODIFY COLUMN status_pinjam ENUM('Tersedia','Dipinjam','Telah Didistribusikan','Tidak untuk Dipinjamkan') NULL DEFAULT 'Tersedia'");
    }
};

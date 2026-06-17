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

        DB::statement("ALTER TABLE barang_kantors MODIFY COLUMN status_pinjam ENUM('Tersedia','Dipinjam','Sedang Dipinjam','Telah Didistribusikan','Tidak untuk Dipinjamkan') NULL DEFAULT 'Tersedia'");
        DB::table('barang_kantors')
            ->where('status_pinjam', 'Dipinjam')
            ->update(['status_pinjam' => 'Sedang Dipinjam']);
        DB::statement("ALTER TABLE barang_kantors MODIFY COLUMN status_pinjam ENUM('Tersedia','Sedang Dipinjam','Telah Didistribusikan','Tidak untuk Dipinjamkan') NULL DEFAULT 'Tersedia'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE barang_kantors MODIFY COLUMN status_pinjam ENUM('Tersedia','Dipinjam','Sedang Dipinjam','Telah Didistribusikan','Tidak untuk Dipinjamkan') NULL DEFAULT 'Tersedia'");
        DB::table('barang_kantors')
            ->where('status_pinjam', 'Sedang Dipinjam')
            ->update(['status_pinjam' => 'Dipinjam']);
        DB::statement("ALTER TABLE barang_kantors MODIFY COLUMN status_pinjam ENUM('Tersedia','Dipinjam','Telah Didistribusikan','Tidak untuk Dipinjamkan') NULL DEFAULT 'Tersedia'");
    }
};

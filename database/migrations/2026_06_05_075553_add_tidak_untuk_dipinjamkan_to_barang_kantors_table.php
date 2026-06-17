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

        DB::statement("ALTER TABLE barang_kantors MODIFY COLUMN status_pinjam ENUM('Tersedia','Sedang Dipinjam','Telah Didistribusikan','Tidak untuk Dipinjamkan') NULL DEFAULT 'Tersedia'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE barang_kantors MODIFY COLUMN status_pinjam ENUM('Tersedia','Sedang Dipinjam','Telah Didistribusikan') NULL DEFAULT 'Tersedia'");
    }
};

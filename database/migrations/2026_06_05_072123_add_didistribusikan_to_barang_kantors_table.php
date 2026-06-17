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

        // MySQL enum tidak bisa dimodifikasi via Blueprint::enum() langsung,
        // gunakan raw SQL untuk menambah nilai baru ke enum
        DB::statement("ALTER TABLE barang_kantors MODIFY COLUMN status_pinjam ENUM('Tersedia','Sedang Dipinjam','Telah Didistribusikan') NULL DEFAULT 'Tersedia'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Kembalikan ke enum semula (pastikan tidak ada baris dengan nilai baru sebelum rollback)
        DB::statement("ALTER TABLE barang_kantors MODIFY COLUMN status_pinjam ENUM('Tersedia','Sedang Dipinjam') NULL DEFAULT 'Tersedia'");
    }
};

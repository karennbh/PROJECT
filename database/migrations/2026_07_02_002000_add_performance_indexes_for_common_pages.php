<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang_kantors', function (Blueprint $table): void {
            $table->index(
                ['kategori_barang', 'jenis_bhp', 'status_barang', 'stok'],
                'barang_kantors_bhp_dropdown_idx'
            );
            $table->index(
                ['kategori_barang', 'status_barang', 'status_pinjam', 'status_penggunaan', 'tanggal_diterima'],
                'barang_kantors_borrowable_idx'
            );
            $table->index(['created_at'], 'barang_kantors_created_at_idx');
        });

        Schema::table('peminjaman_barangs', function (Blueprint $table): void {
            $table->index(['status_pinjam', 'tanggal_pengembalian'], 'peminjaman_status_pengembalian_idx');
            $table->index(['user_id', 'status_pinjam', 'tanggal_pinjam'], 'peminjaman_user_status_tanggal_idx');
            $table->index(['created_at'], 'peminjaman_created_at_idx');
        });

        Schema::table('pemakaian_bhp', function (Blueprint $table): void {
            $table->index(['user_id', 'status', 'tanggal_pemakaian'], 'pemakaian_user_status_tanggal_idx');
            $table->index(['created_at'], 'pemakaian_created_at_idx');
        });

        Schema::table('pembelian_barangs', function (Blueprint $table): void {
            $table->index(['user_id', 'status', 'tanggal_pengajuan'], 'pembelian_user_status_tanggal_idx');
            $table->index(['created_at'], 'pembelian_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('pembelian_barangs', function (Blueprint $table): void {
            $table->dropIndex('pembelian_user_status_tanggal_idx');
            $table->dropIndex('pembelian_created_at_idx');
        });

        Schema::table('pemakaian_bhp', function (Blueprint $table): void {
            $table->dropIndex('pemakaian_user_status_tanggal_idx');
            $table->dropIndex('pemakaian_created_at_idx');
        });

        Schema::table('peminjaman_barangs', function (Blueprint $table): void {
            $table->dropIndex('peminjaman_status_pengembalian_idx');
            $table->dropIndex('peminjaman_user_status_tanggal_idx');
            $table->dropIndex('peminjaman_created_at_idx');
        });

        Schema::table('barang_kantors', function (Blueprint $table): void {
            $table->dropIndex('barang_kantors_bhp_dropdown_idx');
            $table->dropIndex('barang_kantors_borrowable_idx');
            $table->dropIndex('barang_kantors_created_at_idx');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman_barangs', function (Blueprint $table): void {
            $table->id('id_peminjaman');
            $table->foreignId('user_id')
                ->constrained('users', 'id_user')
                ->cascadeOnDelete();
            $table->string('kode_barang');
            $table->enum('kategori_barang', ['aset', 'bhp']);
            $table->string('nama_barang', 50)->nullable();
            $table->date('tanggal_pinjam');
            $table->date('tanggal_pengembalian');
            $table->text('alasan_peminjaman');
            $table->string('bukti_peminjaman')->nullable();
            $table->string('bukti_pengembalian')->nullable();
            $table->integer('jumlah_pinjam')->default(1);
            $table->enum('status_pinjam', [
                'pending',
                'disetujui',
                'menunggu_verifikasi_pengembalian',
                'ditolak',
                'kembali',
                'expired',
            ])->nullable()->default('pending');
            $table->timestamps();

            $table->foreign('kode_barang')
                ->references('kode_barang')
                ->on('barang_kantors')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman_barangs');
    }
};

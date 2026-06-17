<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelian_barangs', function (Blueprint $table): void {
            $table->id('id_pembelian_barang_kantor');
            $table->foreignId('user_id')
                ->constrained('users', 'id_user')
                ->cascadeOnDelete();
            $table->date('tanggal_pengajuan');
            $table->string('nama_barang', 50);
            $table->enum('kategori_barang', ['aset', 'bhp']);
            $table->integer('perkiraan_harga')->default(0);
            $table->integer('jumlah')->default(1);
            $table->integer('sub_total')->default(0);
            $table->string('link_barang')->nullable();
            $table->text('alasan');
            $table->string('bukti_pendukung')->nullable();
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelian_barangs');
    }
};

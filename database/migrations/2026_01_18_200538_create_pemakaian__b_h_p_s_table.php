<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemakaian_bhp', function (Blueprint $table): void {
            $table->id('id_pemakaian');
            $table->foreignId('user_id')
                ->constrained('users', 'id_user')
                ->cascadeOnDelete();
            $table->string('kode_barang');
            $table->string('nama_barang', 50)->nullable();
            $table->date('tanggal_pemakaian');
            $table->integer('jumlah')->default(1);
            $table->text('alasan_kebutuhan');
            $table->string('bukti_pendukung')->nullable();
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
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
        Schema::dropIfExists('pemakaian_bhp');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnals', function (Blueprint $table): void {
            $table->id('id_jurnal_umum');
            $table->string('reff_perolehan_barang')->nullable();
            $table->string('reff_penyusutan', 20)->nullable();
            $table->string('reff_pengisian_kas_kecil')->nullable();
            $table->string('reff_pendapatan_hibah')->nullable();
            $table->date('tanggal');
            $table->text('deskripsi');
            $table->string('tipe_transaksi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnals');
    }
};

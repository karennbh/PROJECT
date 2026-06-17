<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengisian_kas_kecil', function (Blueprint $table): void {
            $table->string('no_transaksi')->primary();
            $table->date('tanggal');
            $table->string('akun_kas_kecil');
            $table->string('akun_sumber_dana');
            $table->integer('nominal');
            $table->text('keterangan')->nullable();
            $table->string('bukti')->nullable();
            $table->timestamps();

            $table->foreign('akun_kas_kecil')
                ->references('kode_akun')
                ->on('coa')
                ->cascadeOnUpdate();
            $table->foreign('akun_sumber_dana')
                ->references('kode_akun')
                ->on('coa')
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengisian_kas_kecil');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coa', function (Blueprint $table): void {
            $table->string('kode_akun')->primary();
            $table->string('nama_akun', 50);
            $table->enum('header_akun', ['Harta', 'Pendapatan', 'Beban']);
            $table->enum('saldo', ['debit', 'kredit']);
            $table->integer('jumlah_saldo')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coa');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_detail', function (Blueprint $table): void {
            $table->id('id_jurnal_detail');
            $table->foreignId('id_jurnal_umum')
                ->constrained('jurnals', 'id_jurnal_umum')
                ->cascadeOnDelete();
            $table->string('kode_akun');
            $table->text('keterangan')->nullable();
            $table->integer('nominal_debit')->default(0);
            $table->integer('nominal_kredit')->default(0);
            $table->timestamps();

            $table->foreign('kode_akun')
                ->references('kode_akun')
                ->on('coa')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_detail');
    }
};

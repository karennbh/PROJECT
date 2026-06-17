<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_aset_tetap', function (Blueprint $table): void {
            $table->string('id_kategori_aset', 20)->primary();
            $table->enum('nama_kategori_aset', ['Kelompok 1', 'Kelompok 2', 'Kelompok 3', 'Kelompok 4']);
            $table->integer('umur_ekonomis')->nullable();
            $table->decimal('tarif_penyusutan', 5, 2)->unsigned();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_aset_tetap');
    }
};

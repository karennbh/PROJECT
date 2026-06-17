<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyusutan_aset_tetap', function (Blueprint $table): void {
            $table->string('id_penyusutan', 20)->primary();
            $table->string('kode_barang');
            $table->string('nama_aset')->nullable();
            $table->enum('status_penggunaan', ['belum_siap_digunakan', 'siap_digunakan'])
                ->default('belum_siap_digunakan');
            $table->date('tanggal_diterima')->nullable();
            $table->integer('harga_perolehan');
            $table->integer('nilai_residu')->nullable();
            $table->integer('umur_ekonomis_tahun');
            $table->integer('beban_penyusutan_bulanan');
            $table->integer('total_biaya_penyusutan')->default(0);
            $table->enum('status_penyusutan', ['Aktif', 'Tidak Aktif'])->default('Aktif');
            $table->text('keterangan_kelengkapan')->nullable();
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
        Schema::dropIfExists('penyusutan_aset_tetap');
    }
};

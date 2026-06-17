<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_kantors', function (Blueprint $table): void {
            $table->enum('kategori_barang', ['aset', 'bhp']);
            $table->enum('jenis_aset', ['sarana_pendidikan_laboratorium', 'inventaris_kantor', 'kendaraan'])->nullable();
            $table->enum('jenis_bhp', ['atk_operasional_kantor', 'inventaris_kantor'])->nullable();
            $table->string('kode_barang')->primary();
            $table->string('barcode')->nullable()->unique();
            $table->string('nama_barang', 50);
            $table->string('foto')->nullable();
            $table->integer('stok')->nullable();
            $table->string('satuan', 50)->nullable();
            $table->string('kategori_aset_id', 20)->nullable();
            $table->integer('umur_ekonomis')->nullable();
            $table->integer('nilai_residu')->nullable();
            $table->enum('status_penggunaan', ['belum_siap_digunakan', 'siap_digunakan'])->nullable();
            $table->date('tanggal_diterima')->nullable();
            $table->integer('harga_perolehan')->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('status_barang', ['Aktif', 'Tidak Aktif'])->default('Aktif');
            $table->enum('status_pinjam', ['Tersedia', 'Sedang Dipinjam'])->nullable()->default('Tersedia');
            $table->unsignedBigInteger('perolehan_barang_detail_id')->nullable();
            $table->timestamps();

            $table->foreign('kategori_aset_id')
                ->references('id_kategori_aset')
                ->on('kategori_aset_tetap')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_kantors');
    }
};

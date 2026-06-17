<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perolehan_barang', function (Blueprint $table): void {
            $table->string('id_perolehan_barang')->primary();
            $table->string('sumber_perolehan', 20)->default('pembelian');
            $table->date('tanggal_pembelian');
            $table->enum('status_penggunaan', ['belum_siap_digunakan', 'siap_digunakan'])
                ->default('belum_siap_digunakan');
            $table->date('tanggal_diterima')->nullable();
            $table->string('nama_pemberi_hibah', 50)->nullable();
            $table->string('foto_nota')->nullable();
            $table->string('bukti_dokumen_hibah')->nullable();
            $table->text('keterangan')->nullable();
            $table->integer('subtotal_barang')->default(0);
            $table->integer('diskon_total')->default(0);
            $table->integer('biaya_lainnya_total')->default(0);
            $table->integer('grand_total')->default(0);
            $table->integer('total_nilai_hibah')->default(0);
            $table->integer('nilai_pengakuan_pendapatan_hibah_uang')->default(0);
            $table->string('pendapatan_hibah_id')->nullable();
            $table->timestamps();
        });

        Schema::create('perolehan_barang_detail', function (Blueprint $table): void {
            $table->id('id_perolehan_barang_detail');
            $table->string('perolehan_barang_id');
            $table->string('nama_barang', 50);
            $table->enum('kategori_barang', ['aset', 'bhp']);
            $table->enum('jenis_aset', ['sarana_pendidikan_laboratorium', 'inventaris_kantor', 'kendaraan'])->nullable();
            $table->enum('jenis_bhp', ['atk_operasional_kantor', 'inventaris_kantor'])->nullable();
            $table->string('kategori_aset_id', 20)->nullable();
            $table->integer('umur_ekonomis')->nullable();
            $table->integer('nilai_residu')->nullable();
            $table->integer('jumlah_perolehan');
            $table->integer('harga_satuan')->default(0);
            $table->integer('total_harga')->default(0);
            $table->decimal('persentase_subtotal', 8, 4)->default(0);
            $table->integer('alokasi_diskon')->default(0);
            $table->integer('alokasi_biaya_lainnya')->default(0);
            $table->string('satuan_perolehan', 20)->nullable();
            $table->integer('harga_perolehan');
            $table->integer('total_harga_perolehan');
            $table->string('kode_barang')->nullable();
            $table->timestamps();

            $table->foreign('perolehan_barang_id')
                ->references('id_perolehan_barang')
                ->on('perolehan_barang')
                ->cascadeOnDelete();
            $table->foreign('kategori_aset_id')
                ->references('id_kategori_aset')
                ->on('kategori_aset_tetap')
                ->nullOnDelete();
            $table->foreign('kode_barang')
                ->references('kode_barang')
                ->on('barang_kantors')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::table('barang_kantors', function (Blueprint $table): void {
            $table->foreign('perolehan_barang_detail_id')
                ->references('id_perolehan_barang_detail')
                ->on('perolehan_barang_detail')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('barang_kantors', function (Blueprint $table): void {
            $table->dropForeign(['perolehan_barang_detail_id']);
        });

        Schema::dropIfExists('perolehan_barang_detail');
        Schema::dropIfExists('perolehan_barang');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pendapatan_hibah')) {
            Schema::create('pendapatan_hibah', function (Blueprint $table): void {
                $table->string('no_hibah')->primary();
                $table->date('tanggal_hibah');
                $table->string('sumber_hibah');
                $table->string('jenis_hibah', 30)->default('hibah_uang');
                $table->string('akun_bank_hibah');
                $table->string('akun_pendapatan_hibah');
                $table->integer('nilai_hibah')->default(0);
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->foreign('akun_bank_hibah')->references('kode_akun')->on('coa')->cascadeOnUpdate();
                $table->foreign('akun_pendapatan_hibah')->references('kode_akun')->on('coa')->cascadeOnUpdate();
            });
        }

        if (Schema::hasTable('jurnals') && ! Schema::hasColumn('jurnals', 'reff_pendapatan_hibah')) {
            Schema::table('jurnals', function (Blueprint $table): void {
                $table->string('reff_pendapatan_hibah')->nullable()->after('reff_pengisian_kas_kecil');
            });
        }

        if (Schema::hasTable('perolehan_barang') && ! Schema::hasColumn('perolehan_barang', 'pendapatan_hibah_id')) {
            Schema::table('perolehan_barang', function (Blueprint $table): void {
                $table->string('pendapatan_hibah_id')->nullable()->after('nilai_pengakuan_pendapatan_hibah_uang');
            });
        }

        if (
            Schema::hasTable('perolehan_barang')
            && Schema::hasTable('pendapatan_hibah')
            && Schema::hasColumn('perolehan_barang', 'pendapatan_hibah_id')
            && ! $this->foreignKeyExists('perolehan_barang', 'perolehan_barang_pendapatan_hibah_id_foreign')
        ) {
            Schema::table('perolehan_barang', function (Blueprint $table): void {
                $table->foreign('pendapatan_hibah_id')
                    ->references('no_hibah')
                    ->on('pendapatan_hibah')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            });
        }

        if (
            Schema::hasTable('jurnals')
            && Schema::hasTable('perolehan_barang')
            && Schema::hasColumn('jurnals', 'reff_perolehan_barang')
            && ! $this->foreignKeyExists('jurnals', 'jurnals_reff_perolehan_barang_foreign')
        ) {
            Schema::table('jurnals', function (Blueprint $table): void {
                $table->foreign('reff_perolehan_barang')
                    ->references('id_perolehan_barang')
                    ->on('perolehan_barang')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            });
        }

        if (
            Schema::hasTable('jurnals')
            && Schema::hasTable('penyusutan_aset_tetap')
            && Schema::hasColumn('jurnals', 'reff_penyusutan')
            && ! $this->foreignKeyExists('jurnals', 'jurnals_reff_penyusutan_foreign')
        ) {
            Schema::table('jurnals', function (Blueprint $table): void {
                $table->foreign('reff_penyusutan')
                    ->references('id_penyusutan')
                    ->on('penyusutan_aset_tetap')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            });
        }

        if (
            Schema::hasTable('jurnals')
            && Schema::hasTable('pengisian_kas_kecil')
            && Schema::hasColumn('jurnals', 'reff_pengisian_kas_kecil')
            && ! $this->foreignKeyExists('jurnals', 'jurnals_reff_pengisian_kas_kecil_foreign')
        ) {
            Schema::table('jurnals', function (Blueprint $table): void {
                $table->foreign('reff_pengisian_kas_kecil')
                    ->references('no_transaksi')
                    ->on('pengisian_kas_kecil')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            });
        }

        if (
            Schema::hasTable('jurnals')
            && Schema::hasTable('pendapatan_hibah')
            && Schema::hasColumn('jurnals', 'reff_pendapatan_hibah')
            && ! $this->foreignKeyExists('jurnals', 'jurnals_reff_pendapatan_hibah_foreign')
        ) {
            Schema::table('jurnals', function (Blueprint $table): void {
                $table->foreign('reff_pendapatan_hibah')
                    ->references('no_hibah')
                    ->on('pendapatan_hibah')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('perolehan_barang') && Schema::hasColumn('perolehan_barang', 'pendapatan_hibah_id')) {
            Schema::table('perolehan_barang', function (Blueprint $table): void {
                $table->dropForeign(['pendapatan_hibah_id']);
                $table->dropColumn('pendapatan_hibah_id');
            });
        }

        if (Schema::hasTable('jurnals') && Schema::hasColumn('jurnals', 'reff_pendapatan_hibah')) {
            Schema::table('jurnals', function (Blueprint $table): void {
                $table->dropForeign(['reff_pendapatan_hibah']);
                $table->dropColumn('reff_pendapatan_hibah');
            });
        }

        Schema::dropIfExists('pendapatan_hibah');
    }

    private function foreignKeyExists(string $table, string $name): bool
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->whereRaw('CONSTRAINT_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $name)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }
};

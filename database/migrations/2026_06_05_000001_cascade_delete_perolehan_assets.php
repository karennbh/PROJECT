<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('barang_kantors')
            ->whereNotNull('perolehan_barang_detail_id')
            ->whereNotExists(function ($query): void {
                $query->select(DB::raw(1))
                    ->from('perolehan_barang_detail')
                    ->whereColumn('perolehan_barang_detail.id_perolehan_barang_detail', 'barang_kantors.perolehan_barang_detail_id');
            })
            ->update(['perolehan_barang_detail_id' => null]);

        Schema::table('barang_kantors', function (Blueprint $table): void {
            $table->dropForeign(['perolehan_barang_detail_id']);

            $table->foreign('perolehan_barang_detail_id')
                ->references('id_perolehan_barang_detail')
                ->on('perolehan_barang_detail')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('barang_kantors', function (Blueprint $table): void {
            $table->dropForeign(['perolehan_barang_detail_id']);

            $table->foreign('perolehan_barang_detail_id')
                ->references('id_perolehan_barang_detail')
                ->on('perolehan_barang_detail')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }
};

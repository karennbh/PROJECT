<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembelian_barangs', function (Blueprint $table): void {
            $table->decimal('perkiraan_harga', 15, 2)->default(0)->change();
            $table->decimal('sub_total', 15, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('pembelian_barangs', function (Blueprint $table): void {
            $table->integer('perkiraan_harga')->default(0)->change();
            $table->integer('sub_total')->default(0)->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyusutan_details', function (Blueprint $table): void {
            $table->id('id_penyusutan_detail');
            $table->string('penyusutan_id', 20);
            $table->date('periode');
            $table->integer('beban_penyusutan_bulanan')->default(0);
            $table->integer('akumulasi')->default(0);
            $table->integer('nilai_buku')->default(0);
            $table->foreignId('jurnal_umum_id')->nullable()
                ->constrained('jurnals', 'id_jurnal_umum')
                ->nullOnDelete();
            $table->timestamps();

            $table->unique(['penyusutan_id', 'periode']);
            $table->foreign('penyusutan_id')
                ->references('id_penyusutan')
                ->on('penyusutan_aset_tetap')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyusutan_details');
    }
};

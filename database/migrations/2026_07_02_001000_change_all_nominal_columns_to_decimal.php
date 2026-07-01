<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<string, array<string, bool>>
     */
    private array $nullableColumns = [
        'barang_kantors' => [
            'nilai_residu' => true,
            'harga_perolehan' => true,
        ],
        'penyusutan_aset_tetap' => [
            'nilai_residu' => true,
        ],
        'perolehan_barang_detail' => [
            'nilai_residu' => true,
        ],
    ];

    /**
     * @var array<string, string[]>
     */
    private array $columnsByTable = [
        'coa' => [
            'jumlah_saldo',
        ],
        'jurnal_detail' => [
            'nominal_debit',
            'nominal_kredit',
        ],
        'barang_kantors' => [
            'nilai_residu',
            'harga_perolehan',
        ],
        'penyusutan_aset_tetap' => [
            'harga_perolehan',
            'nilai_residu',
            'beban_penyusutan_bulanan',
            'total_biaya_penyusutan',
        ],
        'penyusutan_details' => [
            'beban_penyusutan_bulanan',
            'akumulasi',
            'nilai_buku',
        ],
        'perolehan_barang' => [
            'subtotal_barang',
            'diskon_total',
            'biaya_lainnya_total',
            'grand_total',
            'total_nilai_hibah',
            'nilai_pengakuan_pendapatan_hibah_uang',
        ],
        'perolehan_barang_detail' => [
            'nilai_residu',
            'harga_satuan',
            'total_harga',
            'alokasi_diskon',
            'alokasi_biaya_lainnya',
            'harga_perolehan',
            'total_harga_perolehan',
        ],
        'pengisian_kas_kecil' => [
            'nominal',
        ],
        'pendapatan_hibah' => [
            'nilai_hibah',
        ],
    ];

    public function up(): void
    {
        foreach ($this->columnsByTable as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns): void {
                foreach ($columns as $column) {
                    $definition = $table->decimal($column, 15, 2);

                    if ($this->isNullable($tableName, $column)) {
                        $definition->nullable();
                    } else {
                        $definition->default(0);
                    }

                    $definition->change();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->columnsByTable as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns): void {
                foreach ($columns as $column) {
                    $definition = $table->integer($column);

                    if ($this->isNullable($tableName, $column)) {
                        $definition->nullable();
                    } else {
                        $definition->default(0);
                    }

                    $definition->change();
                }
            });
        }
    }

    private function isNullable(string $tableName, string $column): bool
    {
        return $this->nullableColumns[$tableName][$column] ?? false;
    }
};

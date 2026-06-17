<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- Jurnals columns ---\n";
$cols = DB::select('SHOW COLUMNS FROM jurnals');
foreach ($cols as $c) {
    echo "{$c->Field} ({$c->Type})\n";
}
echo "\n--- Foreign keys on jurnals ---\n";
$fks = DB::select("
    SELECT COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_COLUMN_NAME, REFERENCED_TABLE_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_NAME = 'jurnals' AND TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL
");
foreach ($fks as $fk) {
    echo "{$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME} ({$fk->CONSTRAINT_NAME})\n";
}
echo "\n--- Recent Jurnals ---\n";
foreach (\App\Models\JurnalUmum::orderByDesc('id_jurnal_umum')->take(3)->get() as $j) {
    echo "ID: {$j->id_jurnal_umum}, Tipe: {$j->tipe_transaksi}, Reff_Barang_Masuk: {$j->reff_barang_masuk}, Reff_Penyusutan: {$j->reff_penyusutan}\n";
}
echo "\n--- Recent Penyusutan ---\n";
foreach (\App\Models\Penyusutan::orderByDesc('id_penyusutan')->take(3)->get() as $p) {
    echo "ID: {$p->id_penyusutan}, Barang: {$p->barang_kantor_id}\n";
}

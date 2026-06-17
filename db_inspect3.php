<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test the exact query Filament would run
$query = \App\Models\Penyusutan::query();

echo "Count without anything: " . $query->count() . "\n";

// Try eager loading what the table loads
$query->with(['barangKantor']);

$results = $query->get();
echo "Count with relation: " . $results->count() . "\n";

if ($results->count() > 0) {
    foreach ($results as $r) {
        echo "ID: {$r->id_penyusutan}, Barang ID: {$r->barang_kantor_id}\n";
        echo "Barang object: " . ($r->barangKantor ? $r->barangKantor->nama_barang : 'NULL') . "\n";
    }
} else {
    echo "No results.\n";
}

<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$out = [];
$out['columns'] = DB::select('SHOW COLUMNS FROM jurnals');
$out['jurnals'] = \App\Models\JurnalUmum::orderByDesc('id_jurnal_umum')->take(3)->get()->toArray();
$out['penyusutan'] = \App\Models\Penyusutan::orderByDesc('id_penyusutan')->take(3)->get()->toArray();

file_put_contents('inspect.json', json_encode($out, JSON_PRETTY_PRINT));
echo "Done saving to inspect.json.\n";

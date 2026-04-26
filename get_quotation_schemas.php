<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = [
    'zones', 'countries', 'tax_regime', 
    'quotation_headers', 'quotation_details'
];
$result = [];
foreach ($tables as $t) {
    if (\Illuminate\Support\Facades\Schema::hasTable($t)) {
        $result[$t] = \Illuminate\Support\Facades\DB::select('DESCRIBE ' . $t);
    }
}
echo json_encode($result, JSON_PRETTY_PRINT);

<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = [
    'supplier_contracts', 'supplier_contract_items', 'supplier_r_f_q_s', 
    'supplier_rating_criteria_guides', 'supplier_rating_criteria_guide_supplier_scores', 
    'suppliers_rating_criterias', 'supplier_quote_attachments', 'supplier_quote_notes'
];
$result = [];
foreach ($tables as $t) {
    if (\Illuminate\Support\Facades\Schema::hasTable($t)) {
        $result[$t] = \Illuminate\Support\Facades\DB::select('DESCRIBE ' . $t);
    }
}
echo json_encode($result, JSON_PRETTY_PRINT);

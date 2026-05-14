<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$viewDef = DB::select("SELECT pg_get_viewdef('samples_to_analysis_relation_view', true) AS def");
print_r($viewDef);

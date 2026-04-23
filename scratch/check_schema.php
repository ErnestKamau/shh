<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $columns = DB::connection('pgsql_ai')->select("
        SELECT column_name, data_type 
        FROM information_schema.columns 
        WHERE table_schema = 'reporting' 
        AND table_name = 'sync_runs'
        ORDER BY ordinal_position
    ");
    print_r($columns);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

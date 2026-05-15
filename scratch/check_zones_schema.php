<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    $columns = Schema::getColumnListing('zones');
    echo "Columns: " . implode(', ', $columns) . "\n";
    
    $sample = DB::table('zones')->first();
    echo "Sample: " . json_encode($sample) . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

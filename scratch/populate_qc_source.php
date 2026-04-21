<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\QualityControl\Jobs\QcData\CreateQcResultsJob;
use Illuminate\Support\Facades\DB;

$batchIds = [11, 20, 21, 22, 42, 75, 76, 79, 80];

foreach ($batchIds as $id) {
    echo "Processing batch ID: $id...\n";
    try {
        $job = new CreateQcResultsJob($id);
        $job->handle();
        echo "Successfully processed batch $id.\n";
    } catch (\Exception $e) {
        echo "Failed to process batch $id: " . $e->getMessage() . "\n";
    }
}

$count = DB::connection('mysql')->table('qc_results')->count();
echo "\nTotal rows in MySQL qc_results: $count\n";

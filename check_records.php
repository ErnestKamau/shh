<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use Illuminate\Support\Facades\DB;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking database records...\n";

$requestCount = SampleSubmissionRequest::count();
echo "Total SampleSubmissionRequest: " . $requestCount . "\n";

$requestByStatus = SampleSubmissionRequest::select('status', DB::raw('count(*) as total'))
    ->groupBy('status')
    ->get();
echo "SampleSubmissionRequest by Status:\n";
foreach ($requestByStatus as $row) {
    echo "  - " . $row->status . ": " . $row->total . "\n";
}

$formCount = SubmissionFormInstance::count();
echo "Total SubmissionFormInstance: " . $formCount . "\n";

$formByStatus = SubmissionFormInstance::select('status', DB::raw('count(*) as total'))
    ->groupBy('status')
    ->get();
echo "SubmissionFormInstance by Status:\n";
foreach ($formByStatus as $row) {
    echo "  - " . $row->status . ": " . $row->total . "\n";
}

echo "Done.\n";

<?php

use App\Models\BulkImportBatch;
use App\User;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$latestBatch = BulkImportBatch::where('module', 'Personnel')
    ->orWhere('form_type', 'Personnel')
    ->orderBy('created_at', 'desc')
    ->first();

if (!$latestBatch) {
    echo "No recent batch found for Personnel.\n";
    exit;
}

echo "Latest Batch ID: " . $latestBatch->id . "\n";
echo "Created At: " . $latestBatch->created_at . "\n";
echo "Status: " . $latestBatch->status . "\n";
echo "Upserted Summary: " . json_encode($latestBatch->upserted_summary, JSON_PRETTY_PRINT) . "\n";

$emails = $latestBatch->upserted_summary['inserted'] ?? [];

if (empty($emails)) {
    echo "No users were 'inserted' in this batch.\n";
    exit;
}

echo "Found " . count($emails) . " inserted users.\n";
foreach ($emails as $email) {
    echo " - $email\n";
}

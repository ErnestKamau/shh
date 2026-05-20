<?php
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$headers = DB::table('sample_headers')->get(['id', 'batch_code', 'status', 'sample_type_id']);
foreach ($headers as $h) {
    echo "ID: {$h->id} | BatchCode: {$h->batch_code} | Status: {$h->status} | SampleType: {$h->sample_type_id}\n";
}

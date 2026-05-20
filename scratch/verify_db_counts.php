<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DATABASE COUNTS VERIFICATION (PUBLIC SCHEMA) ===\n\n";

// 1. Total active individual sample items (individual_sample_count)
$rep_individual = DB::table('public.sample_details')
    ->join('public.sample_headers', 'public.sample_headers.id', '=', 'public.sample_details.sample_header_id')
    ->where('public.sample_headers.isactive', true)
    ->count();
echo "1. Individual Sample Count: $rep_individual\n";

// 2. Total active sample batches (sample_count_total / batch_count_total)
$rep_batches = DB::table('public.sample_headers')
    ->where('isactive', true)
    ->count();
echo "2. Total Active Sample Batches: $rep_batches\n";

// 3. Sample batches awaiting review/verification/approval
$rep_pending = DB::table('public.sample_headers')
    ->where('isactive', true)
    ->whereIn('status', ['Samples Request Review', 'Sample Verification', 'Sample Approval'])
    ->select('status', DB::raw('count(*) as cnt'))
    ->groupBy('status')
    ->get();
echo "3. Pending Review Batches:\n";
foreach($rep_pending as $p) echo "   * {$p->status}: {$p->cnt}\n";

// 4. Sample count in Lab
$rep_in_lab = DB::table('public.sample_details')
    ->join('public.sample_headers', 'public.sample_headers.id', '=', 'public.sample_details.sample_header_id')
    ->join('public.sample_analysis_stages', 'public.sample_analysis_stages.id', '=', 'public.sample_headers.sample_tracking_stage')
    ->where('public.sample_headers.isactive', true)
    ->where('public.sample_analysis_stages.sample_workflow', 'Samples In Lab')
    ->count();
echo "4. Samples In Lab: $rep_in_lab\n";

// 5. Total active equipment logged
$rep_equipment = DB::table('public.equipment')
    ->where('active', true)
    ->count();
echo "5. Total Active Equipment: $rep_equipment\n";


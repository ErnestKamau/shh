<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if (Schema::hasTable('crm_areas')) {
    echo "crm_areas table EXISTS\n";
    $count = DB::table('crm_areas')->count();
    echo "Count: $count\n";
} else {
    echo "crm_areas table MISSING\n";
}

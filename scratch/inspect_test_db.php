<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

foreach (['gcla', 'gcla_test'] as $dbName) {
    echo "========================================\n";
    echo "Database: {$dbName}\n";
    echo "========================================\n";
    try {
        config(['database.connections.pgsql.database' => $dbName]);
        DB::purge('pgsql');
        DB::reconnect('pgsql');

        $users = DB::table('users')->count();
        $zones = DB::table('zones')->count();
        $companies = DB::table('companies')->count();
        $crmCustomers = DB::table('crm_customers')->count();
        $labs = DB::table('labs')->count();

        echo "Users: {$users}\n";
        echo "Zones: {$zones}\n";
        echo "Companies: {$companies}\n";
        echo "CRM Customers: {$crmCustomers}\n";
        echo "Labs: {$labs}\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

<?php
 
/**
 * Diagnostic and Fix script for Monitoring Module and Equipment Usage
 */
 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
 
echo "--- Monitoring Table Diagnostic ---\n";
 
$connection = DB::getDefaultConnection();
echo "Default Connection: {$connection}\n";
 
$database = DB::connection()->getDatabaseName();
echo "Database Name: {$database}\n";
 
$tableName = 'monitoring_logs';
echo "Checking for table: {$tableName}...\n";
 
if (Schema::hasTable($tableName)) {
    echo "SUCCESS: Table '{$tableName}' exists.\n";
    $count = DB::table($tableName)->count();
    echo "Current row count: {$count}\n";
} else {
    echo "ERROR: Table '{$tableName}' does NOT exist.\n";
    
    echo "Attempting to run migrations...\n";
    try {
        Artisan::call('migrate', ['--force' => true]);
        echo "Migration Output:\n" . Artisan::output() . "\n";
        
        if (Schema::hasTable($tableName)) {
            echo "SUCCESS: Table '{$tableName}' was created.\n";
        } else {
            echo "FAILED: Table '{$tableName}' still does not exist after migration.\n";
        }
    } catch (\Exception $e) {
        echo "EXCEPTION during migration: " . $e->getMessage() . "\n";
    }
}
 
echo "--- Finished Monitoring Diagnostic ---\n\n";

echo "--- Equipment Usage Table Diagnostic ---\n";
$usageTable = 'method_sequence_stage_equipment_usage';
echo "Checking for table: {$usageTable}...\n";

if (Schema::hasTable($usageTable)) {
    echo "SUCCESS: Table '{$usageTable}' exists.\n";
    $columns = Schema::getColumnListing($usageTable);
    echo "Columns: " . implode(', ', $columns) . "\n";
    
    $requiredColumns = ['started_at', 'completed_at', 'started_by_user_id', 'completed_by_user_id'];
    foreach ($requiredColumns as $col) {
        if (Schema::hasColumn($usageTable, $col)) {
            echo "SUCCESS: Column '{$col}' exists.\n";
        } else {
            echo "ERROR: Column '{$col}' does NOT exist!\n";
        }
    }
} else {
    echo "ERROR: Table '{$usageTable}' does NOT exist.\n";
}

echo "--- Finished Equipment Diagnostic ---\n";

<?php

/**
 * Cleanup script to delete users from the latest Excel import batch.
 * 
 * Usage: 
 *   Dry Run: php artisan tinker scratch/delete_imported_users.php
 *   Actual Delete: Set $dryRun = false; below
 */

use App\Models\BulkImportBatch;
use App\User;
use App\UserZoneRelation;
use App\UserDirectorateRelation;
use App\UserLabRelation;
use App\PersonnelWorkHistory;
use Illuminate\Support\Facades\DB;

// CONFIGURATION
$dryRun = false; // SET TO FALSE TO DELETE
$moduleName = 'Personnel';

echo "--- User Import Cleanup Tool ---\n";
echo "Mode: " . ($dryRun ? "DRY RUN (No changes will be made)" : "ACTUAL DELETE") . "\n\n";

// 1. Find latest batch
$latestBatch = BulkImportBatch::whereIn('module', [$moduleName, 'generic', 'personnel', 'Personnel'])
    ->orderBy('created_at', 'desc')
    ->first();

if (!$latestBatch) {
    echo "Error: No recent batch found for module '{$moduleName}' or 'generic'.\n";
    echo "Checking for users created in the last 2 hours as a fallback...\n";
    
    $recentUsers = User::where('created_at', '>=', now()->subHours(2))
        ->get();
    
    if ($recentUsers->isEmpty()) {
        echo "No recently created users found either.\n";
        return;
    }
    
    echo "Found " . $recentUsers->count() . " users created recently.\n";
    $emails = $recentUsers->pluck('email')->toArray();
} else {
    echo "Found Batch: {$latestBatch->id}\n";
    echo "Date: {$latestBatch->created_at}\n";
    echo "Status: {$latestBatch->status}\n";

    $emails = $latestBatch->upserted_summary['inserted'] ?? [];
}

if (empty($emails)) {
    echo "No users were marked as 'inserted' in this batch summary.\n";
    return;
}

echo "Users to process: " . count($emails) . "\n\n";

foreach ($emails as $email) {
    $user = User::where('email', $email)->first();
    
    if (!$user) {
        echo "[SKIP] User with email '{$email}' not found (already deleted?).\n";
        continue;
    }

    echo "[MATCH] User: {$user->name} ({$user->email}) ID: {$user->id}\n";

    if (!$dryRun) {
        try {
            DB::transaction(function() use ($user) {
                // Delete relations manually to be safe (if no cascade)
                UserZoneRelation::where('user_id', $user->id)->delete();
                UserDirectorateRelation::where('user_id', $user->id)->delete();
                UserLabRelation::where('user_id', $user->id)->delete();
                PersonnelWorkHistory::where('user_id', $user->id)->delete();
                
                // Delete the user record
                $user->delete();
            });
            echo "  -> DELETED successfully.\n";
        } catch (\Exception $e) {
            echo "  -> ERROR: {$e->getMessage()}\n";
        }
    } else {
        echo "  -> (Dry run) Would delete this user and its relations.\n";
    }
}

echo "\n--- Finished ---\n";

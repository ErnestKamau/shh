<?php

/**
 * Cleanup script to delete users from an Excel import batch.
 */

use App\Models\BulkImportBatch;
use App\User;
use App\UserZoneRelation;
use App\UserDirectorateRelation;
use App\UserLabRelation;
use App\PersonnelWorkHistory;
use Illuminate\Support\Facades\DB;

// CONFIGURATION
$dryRun = true; // KEEP DRY RUN TRUE UNTIL BATCH IS CONFIRMED
$targetBatchId = null; // Set this to a specific UUID to target one batch

echo "--- User Import Cleanup Tool ---\n";
echo "Mode: " . ($dryRun ? "DRY RUN (No changes will be made)" : "ACTUAL DELETE") . "\n\n";

if (!$targetBatchId) {
    echo "Recent Batches from today:\n";
    $batches = BulkImportBatch::where('created_at', '>=', now()->startOfDay())
        ->orderBy('created_at', 'desc')
        ->get();
    
    if ($batches->isEmpty()) {
        echo "No batches found for today. Checking last 24 hours...\n";
        $batches = BulkImportBatch::where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    foreach ($batches as $b) {
        $summary = $b->upserted_summary ?? [];
        $insertedCount = count($summary['inserted'] ?? []);
        $updatedCount = count($summary['updated'] ?? []);
        echo " - ID: {$b->id} | Time: {$b->created_at} | Module: {$b->module} | Status: {$b->status} | Inserted: {$insertedCount}, Updated: {$updatedCount}\n";
    }
    
    echo "\nLatest batch selected automatically.\n";
    $latestBatch = $batches->first();
} else {
    $latestBatch = BulkImportBatch::find($targetBatchId);
}

if (!$latestBatch) {
    echo "Error: No batch found.\n";
    return;
}

echo "Targeting Batch: {$latestBatch->id}\n";
$summary = $latestBatch->upserted_summary ?? [];
$emails = array_unique(array_merge(
    $summary['inserted'] ?? [],
    $summary['updated'] ?? [] // Include updated ones too just in case
));

if (empty($emails)) {
    echo "No users found in this batch summary.\n";
    return;
}

echo "Total unique users in batch: " . count($emails) . "\n\n";

$count = 0;
foreach ($emails as $email) {
    $user = User::where('email', $email)->first();
    
    if (!$user) {
        continue;
    }

    // Optional: Filter only users created around the batch time to avoid deleting existing users who were just updated
    // But since the user wants to remove the data added, and they said they aren't in the UI, it's likely they are new.
    
    echo "[MATCH] User: {$user->name} ({$user->email})\n";
    $count++;

    if (!$dryRun) {
        try {
            DB::transaction(function() use ($user) {
                UserZoneRelation::where('user_id', $user->id)->delete();
                UserDirectorateRelation::where('user_id', $user->id)->delete();
                UserLabRelation::where('user_id', $user->id)->delete();
                PersonnelWorkHistory::where('user_id', $user->id)->delete();
                $user->delete();
            });
            echo "  -> DELETED.\n";
        } catch (\Exception $e) {
            echo "  -> ERROR: {$e->getMessage()}\n";
        }
    } else {
        echo "  -> (Dry run) Would delete.\n";
    }
}

if ($count === 0) {
    echo "No matching users found in the database for the emails in this batch.\n";
}

echo "\n--- Finished ---\n";

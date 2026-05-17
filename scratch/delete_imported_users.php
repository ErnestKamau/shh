<?php

/**
 * Cleanup script to delete users from specific Excel import batches on the server.
 */

use App\Models\BulkImportBatch;
use App\User;
use App\UserZoneRelation;
use App\UserDirectorateRelation;
use App\UserLabRelation;
use App\PersonnelWorkHistory;
use Illuminate\Support\Facades\DB;

// CONFIGURATION
$dryRun = true; // SET TO FALSE TO DELETE
$manualBatchId = null; // Set to a specific UUID string to override, otherwise targets the latest batch

echo "--- Server User Import Cleanup Tool ---\n";
echo "Mode: " . ($dryRun ? "DRY RUN (No changes will be made)" : "ACTUAL DELETE") . "\n\n";

// 1. Resolve batch
if ($manualBatchId) {
    $batch = BulkImportBatch::find($manualBatchId);
} else {
    // Try to find the latest personnel/generic batch
    $batch = BulkImportBatch::whereIn('module', ['personnel', 'Personnel', 'generic'])
        ->orderBy('created_at', 'desc')
        ->first();
        
    if (!$batch) {
        // Fallback to absolute latest batch
        $batch = BulkImportBatch::orderBy('created_at', 'desc')->first();
    }
}

if (!$batch) {
    echo "Error: No batch found.\n";
    return;
}

echo "Targeting Batch ID: {$batch->id}\n";
echo "Batch Created At  : {$batch->created_at}\n";
echo "Batch Module      : {$batch->module}\n";
echo "Batch Status      : {$batch->status}\n";

$summary = $batch->upserted_summary ?? [];
$insertedCount = count($summary['inserted'] ?? []);
$updatedCount = count($summary['updated'] ?? []);
echo "Batch Upserts     : Inserted: {$insertedCount}, Updated: {$updatedCount}\n\n";

$allEmails = array_unique(array_merge($summary['inserted'] ?? [], $summary['updated'] ?? []));

if (empty($allEmails)) {
    echo "No users found in the specified batches.\n";
    return;
}

echo "Total unique users to process: " . count($allEmails) . "\n\n";

$deletedCount = 0;
foreach ($allEmails as $email) {
    $user = User::where('email', $email)->first();
    if (!$user)
        continue;

    echo "[MATCH] User: {$user->name} ({$user->email})\n";

    if (!$dryRun) {
        try {
            DB::transaction(function () use ($user) {
                UserZoneRelation::where('user_id', $user->id)->delete();
                UserDirectorateRelation::where('user_id', $user->id)->delete();
                UserLabRelation::where('user_id', $user->id)->delete();
                PersonnelWorkHistory::where('user_id', $user->id)->delete();
                $user->delete();
            });
            echo "  -> DELETED.\n";
            $deletedCount++;
        } catch (\Exception $e) {
            echo "  -> ERROR: {$e->getMessage()}\n";
        }
    } else {
        echo "  -> (Dry run) Would delete.\n";
        $deletedCount++;
    }
}

echo "\nTotal users processed: $deletedCount\n";
echo "--- Finished ---\n";

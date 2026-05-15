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
$dryRun = false; // SET TO FALSE TO DELETE
$targetBatchIds = [
    '019e2a71-dce9-7017-b698-9918c3e6c065',
    '019e2a55-b84e-70df-a9fc-8aa8db4797ba'
];

echo "--- Server User Import Cleanup Tool ---\n";
echo "Mode: " . ($dryRun ? "DRY RUN (No changes will be made)" : "ACTUAL DELETE") . "\n\n";

$allEmails = [];
foreach ($targetBatchIds as $id) {
    $batch = BulkImportBatch::find($id);
    if (!$batch) {
        echo "Warning: Batch {$id} not found.\n";
        continue;
    }
    
    echo "Processing Batch: {$id} ({$batch->created_at})\n";
    $summary = $batch->upserted_summary ?? [];
    $emails = array_merge($summary['inserted'] ?? [], $summary['updated'] ?? []);
    $allEmails = array_merge($allEmails, $emails);
}

$allEmails = array_unique($allEmails);

if (empty($allEmails)) {
    echo "No users found in the specified batches.\n";
    return;
}

echo "Total unique users to process: " . count($allEmails) . "\n\n";

$deletedCount = 0;
foreach ($allEmails as $email) {
    $user = User::where('email', $email)->first();
    if (!$user) continue;

    echo "[MATCH] User: {$user->name} ({$user->email})\n";
    
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

<?php

use App\Models\Equipments\Equipment;
use Illuminate\Support\Facades\DB;

// Reset all disposed equipment to active
$count = Equipment::whereNotNull('dispose_date')->count();
echo "Found {$count} disposed items. Resetting...\n";

Equipment::whereNotNull('dispose_date')->update([
    'dispose_date' => null,
    'employee_dispose_id' => null,
    'comment' => null, // Assuming 'comment' holds the reason based on previous blade view
    // 'status' => 'Active' // We need to check if 'status' column exists or if it's derived
]);

// Determine if we need to update a status column
$sample = Equipment::first();
if ($sample) {
    if (isset($sample->status)) {
         Equipment::where('status', 'Disposed')->update(['status' => 'Active']);
         echo "Updated status column to Active.\n";
    }
    // Also set active flag if exists
    if (isset($sample->active)) {
         Equipment::query()->update(['active' => 1]);
         echo "Updated active flag to 1.\n";
    }
}

echo "Reset complete.\n";

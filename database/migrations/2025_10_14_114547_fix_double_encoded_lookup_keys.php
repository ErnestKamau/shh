<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix double-encoded JSON keys in lookup_table_entries
        $entries = DB::table('lookup_table_entries')->get();
        
        foreach ($entries as $entry) {
            $keys = $entry->keys;
            
            // Check if keys are double-encoded (starts with " and contains \")
            if (is_string($keys) && str_starts_with($keys, '"') && str_contains($keys, '\\"')) {
                try {
                    // Decode once to fix the double-encoding
                    $decodedKeys = json_decode($keys, true);
                    
                    if ($decodedKeys !== null && is_array($decodedKeys)) {
                        // Convert numeric values to strings for consistency
                        $normalizedKeys = [];
                        foreach ($decodedKeys as $key => $value) {
                            $normalizedKeys[$key] = (string) $value;
                        }
                        
                        // Update the entry with properly encoded keys
                        DB::table('lookup_table_entries')
                            ->where('id', $entry->id)
                            ->update(['keys' => json_encode($normalizedKeys)]);
                        
                        echo "Fixed double-encoded keys for entry ID: {$entry->id}\n";
                    }
                } catch (\Exception $e) {
                    echo "Error fixing entry ID {$entry->id}: " . $e->getMessage() . "\n";
                }
            }
        }
        
        echo "Migration completed. Double-encoded lookup keys have been fixed.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible as it fixes corrupted data
        // If you need to reverse it, you would need to restore from backup
        echo "This migration cannot be reversed. Please restore from backup if needed.\n";
    }
};
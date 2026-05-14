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
        Schema::table('sample_headers', function (Blueprint $table) {
            // First, convert the column to text to safely handle mixed data
            DB::statement('ALTER TABLE sample_headers ALTER COLUMN sample_tracking_stage TYPE TEXT USING sample_tracking_stage::text');
            
            // Map known legacy IDs to their new UUIDs based on names
            $mappings = [
                '20007' => 'Samples Request Review',
                '20008' => 'Samples In Lab',
            ];

            foreach ($mappings as $oldId => $name) {
                DB::statement("
                    UPDATE sample_headers 
                    SET sample_tracking_stage = (SELECT id::text FROM sample_analysis_stages WHERE name = ? LIMIT 1)
                    WHERE sample_tracking_stage = ?
                ", [$name, $oldId]);
            }

            // Finally, convert the column to UUID, handling any remaining non-UUID values as NULL
            DB::statement("ALTER TABLE sample_headers ALTER COLUMN sample_tracking_stage TYPE UUID USING (CASE WHEN sample_tracking_stage ~ '^[0-9a-fA-F-]{36}$' THEN sample_tracking_stage::uuid ELSE NULL END)");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_headers', function (Blueprint $table) {
            DB::statement('ALTER TABLE sample_headers ALTER COLUMN sample_tracking_stage TYPE INTEGER USING sample_tracking_stage::text::integer');
        });
    }
};

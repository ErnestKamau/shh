<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the foreign key constraint if it exists
        DB::statement('ALTER TABLE analysis_elements DROP CONSTRAINT IF EXISTS analysis_elements_lab_section_id_foreign');
        DB::statement('ALTER TABLE analysis_elements DROP CONSTRAINT IF EXISTS fk_analysis_elements_lab_section_id');

        DB::statement('ALTER TABLE analysis_elements ALTER COLUMN lab_section_id DROP DEFAULT');
        DB::statement('ALTER TABLE analysis_elements ALTER COLUMN lab_section_id DROP NOT NULL');
        
        // Alter type of lab_section_id to uuid. If it has a UUID string or integer, map accordingly.
        DB::statement(
            "ALTER TABLE analysis_elements
             ALTER COLUMN lab_section_id TYPE uuid
             USING (
                CASE
                    WHEN lab_section_id::text IS NULL THEN NULL
                    WHEN lab_section_id::text ~ '^[0-9a-fA-F-]{36}$' THEN lab_section_id::text::uuid
                    ELSE NULL
                END
             )"
        );

        // Add foreign key constraint to lab_sections
        Schema::table('analysis_elements', function (Blueprint $table): void {
            $table->foreign('lab_section_id', 'fk_analysis_elements_lab_section_id')
                ->references('id')
                ->on('lab_sections')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE analysis_elements DROP CONSTRAINT IF EXISTS fk_analysis_elements_lab_section_id');

        DB::statement('ALTER TABLE analysis_elements ALTER COLUMN lab_section_id DROP DEFAULT');
        DB::statement('ALTER TABLE analysis_elements ALTER COLUMN lab_section_id DROP NOT NULL');
        DB::statement(
            "ALTER TABLE analysis_elements
             ALTER COLUMN lab_section_id TYPE integer
             USING (
                CASE
                    WHEN lab_section_id IS NULL THEN NULL
                    WHEN lab_section_id::text ~ '^[0-9]+$' THEN lab_section_id::text::integer
                    ELSE NULL
                END
             )"
        );
    }
};

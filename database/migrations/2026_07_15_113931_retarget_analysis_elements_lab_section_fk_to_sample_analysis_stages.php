<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Operational lab sections live on sample_analysis_stages.
     * analysis_elements.lab_section_id was incorrectly FKd to monitoring lab_sections.
     */
    public function up(): void
    {
        if (! Schema::hasTable('analysis_elements') || ! Schema::hasColumn('analysis_elements', 'lab_section_id')) {
            return;
        }

        DB::statement('ALTER TABLE analysis_elements DROP CONSTRAINT IF EXISTS analysis_elements_lab_section_id_foreign');
        DB::statement('ALTER TABLE analysis_elements DROP CONSTRAINT IF EXISTS fk_analysis_elements_lab_section_id');

        // Clear values that are not operational lab sections (sample_analysis_stages ids).
        DB::statement(
            'UPDATE analysis_elements ae
             SET lab_section_id = NULL
             WHERE lab_section_id IS NOT NULL
               AND NOT EXISTS (
                   SELECT 1
                   FROM sample_analysis_stages sas
                   WHERE sas.id = ae.lab_section_id
               )'
        );

        Schema::table('analysis_elements', function (Blueprint $table): void {
            $table->foreign('lab_section_id', 'fk_analysis_elements_lab_section_id')
                ->references('id')
                ->on('sample_analysis_stages')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('analysis_elements') || ! Schema::hasColumn('analysis_elements', 'lab_section_id')) {
            return;
        }

        DB::statement('ALTER TABLE analysis_elements DROP CONSTRAINT IF EXISTS fk_analysis_elements_lab_section_id');

        if (Schema::hasTable('lab_sections')) {
            DB::statement(
                'UPDATE analysis_elements ae
                 SET lab_section_id = NULL
                 WHERE lab_section_id IS NOT NULL
                   AND NOT EXISTS (
                       SELECT 1
                       FROM lab_sections ls
                       WHERE ls.id = ae.lab_section_id
                   )'
            );

            Schema::table('analysis_elements', function (Blueprint $table): void {
                $table->foreign('lab_section_id', 'fk_analysis_elements_lab_section_id')
                    ->references('id')
                    ->on('lab_sections')
                    ->nullOnDelete();
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        DB::statement('ALTER TABLE analysis_elements DROP CONSTRAINT IF EXISTS fk_analysis_elements_method');
        DB::statement('ALTER TABLE analysis_elements DROP CONSTRAINT IF EXISTS fk_analysis_elements_ltm_method_id');

        DB::statement('ALTER TABLE analysis_elements ALTER COLUMN method DROP DEFAULT');
        DB::statement('ALTER TABLE analysis_elements ALTER COLUMN method DROP NOT NULL');
        DB::statement(
            "ALTER TABLE analysis_elements
             ALTER COLUMN method TYPE uuid
             USING (
                CASE
                    WHEN method IS NULL THEN NULL
                    WHEN method::text ~* '^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$' THEN method::text::uuid
                    ELSE NULL
                END
             )"
        );

        DB::statement('ALTER TABLE analysis_elements ALTER COLUMN ltm_method_id DROP DEFAULT');
        DB::statement('ALTER TABLE analysis_elements ALTER COLUMN ltm_method_id DROP NOT NULL');
        DB::statement(
            "ALTER TABLE analysis_elements
             ALTER COLUMN ltm_method_id TYPE uuid
             USING (
                CASE
                    WHEN ltm_method_id IS NULL THEN NULL
                    WHEN ltm_method_id::text ~* '^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$' THEN ltm_method_id::text::uuid
                    ELSE NULL
                END
             )"
        );

        Schema::table('analysis_elements', function (Blueprint $table): void {
            $table->foreign('method', 'fk_analysis_elements_method')
                ->references('id')
                ->on('analysis_methods')
                ->nullOnDelete();

            $table->foreign('ltm_method_id', 'fk_analysis_elements_ltm_method_id')
                ->references('id')
                ->on('analysis_methods')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE analysis_elements DROP CONSTRAINT IF EXISTS fk_analysis_elements_method');
        DB::statement('ALTER TABLE analysis_elements DROP CONSTRAINT IF EXISTS fk_analysis_elements_ltm_method_id');

        DB::statement('ALTER TABLE analysis_elements ALTER COLUMN method DROP DEFAULT');
        DB::statement('ALTER TABLE analysis_elements ALTER COLUMN method DROP NOT NULL');
        DB::statement(
            "ALTER TABLE analysis_elements
             ALTER COLUMN method TYPE integer
             USING (
                CASE
                    WHEN method IS NULL THEN NULL
                    WHEN method::text ~ '^[0-9]+$' THEN method::text::integer
                    ELSE NULL
                END
             )"
        );

        DB::statement('ALTER TABLE analysis_elements ALTER COLUMN ltm_method_id DROP DEFAULT');
        DB::statement('ALTER TABLE analysis_elements ALTER COLUMN ltm_method_id DROP NOT NULL');
        DB::statement(
            "ALTER TABLE analysis_elements
             ALTER COLUMN ltm_method_id TYPE integer
             USING (
                CASE
                    WHEN ltm_method_id IS NULL THEN NULL
                    WHEN ltm_method_id::text ~ '^[0-9]+$' THEN ltm_method_id::text::integer
                    ELSE NULL
                END
             )"
        );
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BelongsToMany::sync() inserts only the FK columns on these pivots.
     * Without a DB default on id, PostgreSQL rejects the row (NOT NULL on id).
     */
    public function up(): void
    {
        foreach ([
            'submission_form_sample_analysis_stage',
            'sample_to_sample_analysis_stages',
        ] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::statement(sprintf(
                'ALTER TABLE %s ALTER COLUMN id SET DEFAULT gen_random_uuid()',
                $table
            ));
        }
    }

    public function down(): void
    {
        foreach ([
            'submission_form_sample_analysis_stage',
            'sample_to_sample_analysis_stages',
        ] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::statement(sprintf(
                'ALTER TABLE %s ALTER COLUMN id DROP DEFAULT',
                $table
            ));
        }
    }
};

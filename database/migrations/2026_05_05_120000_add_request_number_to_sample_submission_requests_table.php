<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add nullable integer column first
        DB::statement('ALTER TABLE sample_submission_requests ADD COLUMN IF NOT EXISTS request_number INTEGER');

        // Create a dedicated sequence
        DB::statement("CREATE SEQUENCE IF NOT EXISTS sample_submission_requests_rn_seq START 1");

        // Backfill existing rows ordered by created_at
        DB::statement("
            UPDATE sample_submission_requests
            SET request_number = subq.rn
            FROM (
                SELECT id, ROW_NUMBER() OVER (ORDER BY created_at) AS rn
                FROM sample_submission_requests
            ) subq
            WHERE sample_submission_requests.id = subq.id
              AND sample_submission_requests.request_number IS NULL
        ");

        // Advance the sequence past the highest existing value
        DB::statement("
            SELECT setval(
                'sample_submission_requests_rn_seq',
                COALESCE((SELECT MAX(request_number) FROM sample_submission_requests), 0) + 1,
                false
            )
        ");

        // Bind the sequence as default and enforce NOT NULL
        DB::statement("ALTER TABLE sample_submission_requests ALTER COLUMN request_number SET DEFAULT nextval('sample_submission_requests_rn_seq')");
        DB::statement("ALTER TABLE sample_submission_requests ALTER COLUMN request_number SET NOT NULL");

        // Unique index
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS sample_submission_requests_request_number_unique ON sample_submission_requests (request_number)");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sample_submission_requests ALTER COLUMN request_number DROP DEFAULT");
        DB::statement("ALTER TABLE sample_submission_requests DROP COLUMN IF EXISTS request_number");
        DB::statement("DROP SEQUENCE IF EXISTS sample_submission_requests_rn_seq");
    }
};

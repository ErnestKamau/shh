<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'pgsql_ai';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = $this->getConnection();
        $reportingSchema = env('AI_REPORTING_SCHEMA', 'reporting');
        $aiSchema = env('AI_SCHEMA', 'ai');

        // 1. Ensure Reporting & AI Schemas Exist
        DB::connection($connection)->statement("CREATE SCHEMA IF NOT EXISTS $reportingSchema");
        DB::connection($connection)->statement("CREATE SCHEMA IF NOT EXISTS $aiSchema");

        // 2. etl_index_state table
        if (!Schema::connection($connection)->hasTable("$reportingSchema.etl_index_state")) {
            DB::connection($connection)->statement("
                CREATE TABLE $reportingSchema.etl_index_state (
                    table_key TEXT PRIMARY KEY,
                    last_etl_completed_at TIMESTAMP,
                    rows_synced_last_run INTEGER DEFAULT 0,
                    etl_status TEXT DEFAULT 'idle',
                    etl_error TEXT,
                    last_indexed_at TIMESTAMP,
                    last_source_watermark BIGINT DEFAULT 0,
                    chunks_produced_last INTEGER DEFAULT 0,
                    embeddings_produced_last INTEGER DEFAULT 0,
                    index_status TEXT DEFAULT 'idle',
                    index_error TEXT,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                )
            ");

            DB::connection($connection)->statement(
                "CREATE INDEX idx_etl_index_state_status ON $reportingSchema.etl_index_state (etl_status, index_status)"
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = $this->getConnection();
        $reportingSchema = env('AI_REPORTING_SCHEMA', 'reporting');
        DB::connection($connection)->statement("DROP TABLE IF EXISTS $reportingSchema.etl_index_state CASCADE");
    }
};

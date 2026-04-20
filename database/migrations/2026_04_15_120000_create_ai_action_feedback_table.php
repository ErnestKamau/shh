<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql_ai';

    public function up(): void
    {
        $connection = $this->getConnection();

        DB::connection($connection)->statement('CREATE SCHEMA IF NOT EXISTS ai');

        if (!Schema::connection($connection)->hasTable('ai.ai_action_feedback')) {
            DB::connection($connection)->statement("
                CREATE TABLE ai.ai_action_feedback (
                    id SERIAL PRIMARY KEY,
                    trace_id TEXT NOT NULL,
                    decision_id TEXT,
                    entity_id BIGINT NOT NULL,
                    module TEXT NOT NULL,
                    suggested_action TEXT NOT NULL,
                    features_snapshot JSONB NOT NULL DEFAULT '{}'::jsonb,
                    model_version TEXT NOT NULL,
                    decision_output JSONB NOT NULL DEFAULT '{}'::jsonb,
                    user_action TEXT,
                    outcome_result TEXT,
                    user_feedback_text TEXT,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP
                )
            ");

            DB::connection($connection)->statement(
                "CREATE INDEX idx_action_feedback_module ON ai.ai_action_feedback (module)"
            );
            DB::connection($connection)->statement(
                "CREATE INDEX idx_action_feedback_created_at ON ai.ai_action_feedback (created_at DESC)"
            );
            DB::connection($connection)->statement(
                "CREATE INDEX idx_action_feedback_trace_id ON ai.ai_action_feedback (trace_id)"
            );
        }
    }

    public function down(): void
    {
        $connection = $this->getConnection();
        DB::connection($connection)->statement('DROP TABLE IF EXISTS ai.ai_action_feedback CASCADE');
    }
};

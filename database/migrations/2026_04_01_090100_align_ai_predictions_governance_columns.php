<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'pgsql_ai';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = $this->getConnection();

        DB::connection($connection)->statement("\n            ALTER TABLE ai.ai_predictions\n            ADD COLUMN IF NOT EXISTS feature_snapshot_version TEXT,\n            ADD COLUMN IF NOT EXISTS feature_snapshot JSONB,\n            ADD COLUMN IF NOT EXISTS prompt_context JSONB,\n            ADD COLUMN IF NOT EXISTS user_action TEXT,\n            ADD COLUMN IF NOT EXISTS user_feedback TEXT,\n            ADD COLUMN IF NOT EXISTS actor_id BIGINT,\n            ADD COLUMN IF NOT EXISTS action_at TIMESTAMP,\n            ADD COLUMN IF NOT EXISTS model_name TEXT,\n            ADD COLUMN IF NOT EXISTS degraded_mode BOOLEAN DEFAULT FALSE,\n            ADD COLUMN IF NOT EXISTS recommendation TEXT\n        ");

        DB::connection($connection)->statement(
            'CREATE INDEX IF NOT EXISTS idx_ai_predictions_action_at ON ai.ai_predictions (action_at DESC)'
        );
        DB::connection($connection)->statement(
            'CREATE INDEX IF NOT EXISTS idx_ai_predictions_user_action ON ai.ai_predictions (user_action)'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = $this->getConnection();

        DB::connection($connection)->statement('DROP INDEX IF EXISTS ai.idx_ai_predictions_action_at');
        DB::connection($connection)->statement('DROP INDEX IF EXISTS ai.idx_ai_predictions_user_action');
        DB::connection($connection)->statement("\n            ALTER TABLE ai.ai_predictions\n            DROP COLUMN IF EXISTS feature_snapshot_version,\n            DROP COLUMN IF EXISTS feature_snapshot,\n            DROP COLUMN IF EXISTS prompt_context,\n            DROP COLUMN IF EXISTS user_action,\n            DROP COLUMN IF EXISTS user_feedback,\n            DROP COLUMN IF EXISTS actor_id,\n            DROP COLUMN IF EXISTS action_at,\n            DROP COLUMN IF EXISTS model_name,\n            DROP COLUMN IF EXISTS degraded_mode,\n            DROP COLUMN IF EXISTS recommendation\n        ");
    }
};

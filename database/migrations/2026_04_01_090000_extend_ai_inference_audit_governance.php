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

        DB::connection($connection)->statement("\n            ALTER TABLE ai.ai_inference_audit\n            ADD COLUMN IF NOT EXISTS feature_snapshot_id INT,\n            ADD COLUMN IF NOT EXISTS input_data JSONB,\n            ADD COLUMN IF NOT EXISTS recommendation TEXT,\n            ADD COLUMN IF NOT EXISTS user_override BOOLEAN DEFAULT FALSE,\n            ADD COLUMN IF NOT EXISTS actor TEXT\n        ");

        DB::connection($connection)->statement(
            'CREATE INDEX IF NOT EXISTS idx_inference_audit_snapshot ON ai.ai_inference_audit (feature_snapshot_id)'
        );
        DB::connection($connection)->statement(
            'CREATE INDEX IF NOT EXISTS idx_inference_audit_actor ON ai.ai_inference_audit (actor)'
        );
        DB::connection($connection)->statement(
            'CREATE INDEX IF NOT EXISTS idx_inference_audit_override ON ai.ai_inference_audit (user_override)'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = $this->getConnection();

        DB::connection($connection)->statement('DROP INDEX IF EXISTS ai.idx_inference_audit_snapshot');
        DB::connection($connection)->statement('DROP INDEX IF EXISTS ai.idx_inference_audit_actor');
        DB::connection($connection)->statement('DROP INDEX IF EXISTS ai.idx_inference_audit_override');
        DB::connection($connection)->statement("\n            ALTER TABLE ai.ai_inference_audit\n            DROP COLUMN IF EXISTS feature_snapshot_id,\n            DROP COLUMN IF EXISTS input_data,\n            DROP COLUMN IF EXISTS recommendation,\n            DROP COLUMN IF EXISTS user_override,\n            DROP COLUMN IF EXISTS actor\n        ");
    }
};

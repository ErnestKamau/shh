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
    * Creates AI feature tables in the PostgreSQL ai schema.
     *
     * @return void
     */
    public function up(): void
    {
        $connection = $this->getConnection();
        
        // Ensure ai schema exists
        DB::connection($connection)->statement('CREATE SCHEMA IF NOT EXISTS ai');

        // 1. Feature Snapshots
        if (!Schema::connection($connection)->hasTable('ai.ai_feature_snapshots')) {
            DB::connection($connection)->statement("
                CREATE TABLE ai.ai_feature_snapshots (
                    id SERIAL PRIMARY KEY,
                    snapshot_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    description TEXT,
                    metadata JSONB,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            DB::connection($connection)->statement(
                "CREATE INDEX idx_feature_snapshots_time ON ai.ai_feature_snapshots (snapshot_time DESC)"
            );
        }

        // 2. Sample Features
        if (!Schema::connection($connection)->hasTable('ai.ai_sample_features')) {
            DB::connection($connection)->statement("
                CREATE TABLE ai.ai_sample_features (
                    id SERIAL PRIMARY KEY,
                    sample_id BIGINT NOT NULL,
                    snapshot_id INT NOT NULL REFERENCES ai.ai_feature_snapshots(id) ON DELETE CASCADE,
                    tat_days FLOAT,
                    stage_count INT,
                    priority_score INT,
                    rework_flag BOOLEAN DEFAULT FALSE,
                    is_qc_batch BOOLEAN DEFAULT FALSE,
                    processing_delay_days FLOAT,
                    approval_delay_days FLOAT,
                    customer_id BIGINT,
                    feature_version TEXT DEFAULT 'v1.0',
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT uq_sample_features_snapshot UNIQUE (sample_id, snapshot_id)
                )
            ");
            
            DB::connection($connection)->statement(
                "CREATE INDEX idx_sample_features_sample_id ON ai.ai_sample_features (sample_id)"
            );
            DB::connection($connection)->statement(
                "CREATE INDEX idx_sample_features_snapshot_id ON ai.ai_sample_features (snapshot_id)"
            );
            DB::connection($connection)->statement(
                "CREATE INDEX idx_sample_features_tat ON ai.ai_sample_features (tat_days) WHERE tat_days IS NOT NULL"
            );
        }

        // 3. Equipment Features
        if (!Schema::connection($connection)->hasTable('ai.ai_equipment_features')) {
            DB::connection($connection)->statement("
                CREATE TABLE ai.ai_equipment_features (
                    id SERIAL PRIMARY KEY,
                    equipment_id BIGINT NOT NULL,
                    snapshot_id INT NOT NULL REFERENCES ai.ai_feature_snapshots(id) ON DELETE CASCADE,
                    days_since_service INT,
                    days_until_due INT,
                    failure_rate FLOAT,
                    usage_frequency FLOAT,
                    maintenance_count INT DEFAULT 0,
                    calibration_count INT DEFAULT 0,
                    verification_count INT DEFAULT 0,
                    risk_score FLOAT,
                    feature_version TEXT DEFAULT 'v1.0',
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT uq_equipment_features_snapshot UNIQUE (equipment_id, snapshot_id)
                )
            ");
            
            DB::connection($connection)->statement(
                "CREATE INDEX idx_equipment_features_equipment_id ON ai.ai_equipment_features (equipment_id)"
            );
            DB::connection($connection)->statement(
                "CREATE INDEX idx_equipment_features_snapshot_id ON ai.ai_equipment_features (snapshot_id)"
            );
        }

        // 4. QC Features
        if (!Schema::connection($connection)->hasTable('ai.ai_qc_features')) {
            DB::connection($connection)->statement("
                CREATE TABLE ai.ai_qc_features (
                    id SERIAL PRIMARY KEY,
                    qc_result_id BIGINT NOT NULL,
                    snapshot_id INT NOT NULL REFERENCES ai.ai_feature_snapshots(id) ON DELETE CASCADE,
                    cv_percent FLOAT,
                    z_score FLOAT,
                    moving_avg_10 FLOAT,
                    moving_std_10 FLOAT,
                    trend_direction TEXT,
                    within_control_limits BOOLEAN DEFAULT TRUE,
                    westgard_violation BOOLEAN DEFAULT FALSE,
                    outlier_flag BOOLEAN DEFAULT FALSE,
                    analyte_id BIGINT,
                    qc_level TEXT,
                    feature_version TEXT DEFAULT 'v1.0',
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT uq_qc_features_snapshot UNIQUE (qc_result_id, snapshot_id)
                )
            ");
            
            DB::connection($connection)->statement(
                "CREATE INDEX idx_qc_features_qc_result_id ON ai.ai_qc_features (qc_result_id)"
            );
            DB::connection($connection)->statement(
                "CREATE INDEX idx_qc_features_snapshot_id ON ai.ai_qc_features (snapshot_id)"
            );
            DB::connection($connection)->statement(
                "CREATE INDEX idx_qc_features_outliers ON ai.ai_qc_features (outlier_flag) WHERE outlier_flag = TRUE"
            );
        }

        // 5. Prediction Runs
        if (!Schema::connection($connection)->hasTable('ai.ai_prediction_runs')) {
            DB::connection($connection)->statement("
                CREATE TABLE ai.ai_prediction_runs (
                    id SERIAL PRIMARY KEY,
                    model_name TEXT NOT NULL,
                    model_version TEXT NOT NULL,
                    snapshot_id INT REFERENCES ai.ai_feature_snapshots(id) ON DELETE SET NULL,
                    prediction_type TEXT,
                    entity_id BIGINT,
                    predicted_value FLOAT,
                    confidence_score FLOAT,
                    model_path TEXT,
                    hyperparameters JSONB,
                    run_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    inference_duration_ms INT,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            DB::connection($connection)->statement(
                "CREATE INDEX idx_prediction_runs_model ON ai.ai_prediction_runs (model_name, model_version)"
            );
            DB::connection($connection)->statement(
                "CREATE INDEX idx_prediction_runs_snapshot ON ai.ai_prediction_runs (snapshot_id)"
            );
            DB::connection($connection)->statement(
                "CREATE INDEX idx_prediction_runs_time ON ai.ai_prediction_runs (run_time DESC)"
            );
        }

        // 6. Feedback
        if (!Schema::connection($connection)->hasTable('ai.ai_feedback')) {
            DB::connection($connection)->statement("
                CREATE TABLE ai.ai_feedback (
                    id SERIAL PRIMARY KEY,
                    prediction_id INT REFERENCES ai.ai_prediction_runs(id) ON DELETE CASCADE,
                    actual_value FLOAT,
                    prediction_error FLOAT,
                    absolute_error FLOAT,
                    user_feedback TEXT,
                    feedback_type TEXT,
                    user_id BIGINT,
                    collected_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            DB::connection($connection)->statement(
                "CREATE INDEX idx_feedback_prediction_id ON ai.ai_feedback (prediction_id)"
            );
            DB::connection($connection)->statement(
                "CREATE INDEX idx_feedback_collected_at ON ai.ai_feedback (collected_at DESC)"
            );
        }

        // 7. Model Registry
        if (!Schema::connection($connection)->hasTable('ai.ai_model_registry')) {
            DB::connection($connection)->statement("
                CREATE TABLE ai.ai_model_registry (
                    id                          SERIAL PRIMARY KEY,
                    model_name                  TEXT NOT NULL,
                    model_type                  TEXT NOT NULL,
                    version                     TEXT NOT NULL,
                    framework                   TEXT,
                    artifact_path               TEXT,
                    feature_snapshot_id         INT REFERENCES ai.ai_feature_snapshots(id) ON DELETE SET NULL,
                    training_rows               INT,
                    training_duration_seconds   FLOAT,
                    hyperparameters             JSONB,
                    metrics                     JSONB,
                    is_active                   BOOLEAN NOT NULL DEFAULT FALSE,
                    is_deprecated               BOOLEAN NOT NULL DEFAULT FALSE,
                    deployed_at                 TIMESTAMP,
                    created_at                  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at                  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE (model_type, version)
                )
            ");

            DB::connection($connection)->statement(
                "CREATE INDEX idx_model_registry_type ON ai.ai_model_registry (model_type)"
            );
            DB::connection($connection)->statement(
                "CREATE INDEX idx_model_registry_active ON ai.ai_model_registry (model_type, is_active) WHERE is_active = TRUE"
            );
        }

        // 8. Inference Audit
        if (!Schema::connection($connection)->hasTable('ai.ai_inference_audit')) {
            DB::connection($connection)->statement("
                CREATE TABLE ai.ai_inference_audit (
                    id                  SERIAL PRIMARY KEY,
                    model_registry_id   INT REFERENCES ai.ai_model_registry(id) ON DELETE SET NULL,
                    model_name          TEXT NOT NULL,
                    model_version       TEXT,
                    entity_type         TEXT NOT NULL,
                    entity_id           BIGINT,
                    input_features      JSONB,
                    prediction          JSONB,
                    confidence          FLOAT,
                    latency_ms          INT,
                    request_source      TEXT,
                    requested_by        TEXT,
                    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                )
            ");

            DB::connection($connection)->statement(
                "CREATE INDEX idx_inference_audit_entity ON ai.ai_inference_audit (entity_type, entity_id)"
            );
            DB::connection($connection)->statement(
                "CREATE INDEX idx_inference_audit_model ON ai.ai_inference_audit (model_registry_id)"
            );
            DB::connection($connection)->statement(
                "CREATE INDEX idx_inference_audit_created ON ai.ai_inference_audit (created_at DESC)"
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $connection = $this->getConnection();
        
        // Drop tables in reverse order (respecting foreign key constraints)
        DB::connection($connection)->statement('DROP TABLE IF EXISTS ai.ai_inference_audit CASCADE');
        DB::connection($connection)->statement('DROP TABLE IF EXISTS ai.ai_model_registry CASCADE');
        DB::connection($connection)->statement('DROP TABLE IF EXISTS ai.ai_feedback CASCADE');
        DB::connection($connection)->statement('DROP TABLE IF EXISTS ai.ai_prediction_runs CASCADE');
        DB::connection($connection)->statement('DROP TABLE IF EXISTS ai.ai_qc_features CASCADE');
        DB::connection($connection)->statement('DROP TABLE IF EXISTS ai.ai_equipment_features CASCADE');
        DB::connection($connection)->statement('DROP TABLE IF EXISTS ai.ai_sample_features CASCADE');
        DB::connection($connection)->statement('DROP TABLE IF EXISTS ai.ai_feature_snapshots CASCADE');
    }
};

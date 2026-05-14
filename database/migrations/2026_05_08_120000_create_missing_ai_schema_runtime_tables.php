<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS reporting');
        DB::statement('CREATE SCHEMA IF NOT EXISTS ai');

        try {
            DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
        } catch (Throwable $exception) {
            report($exception);
        }

        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS ai.ai_request_logs (
                id BIGSERIAL PRIMARY KEY,
                trace_id VARCHAR(64),
                query TEXT NOT NULL,
                mode VARCHAR(32),
                route_name VARCHAR(128),
                routing_tier VARCHAR(16),
                latency_ms INTEGER,
                confidence DOUBLE PRECISION DEFAULT 0,
                success BOOLEAN DEFAULT TRUE,
                error_message TEXT,
                company_id VARCHAR(128),
                user_id VARCHAR(128),
                session_id VARCHAR(128),
                response_preview TEXT,
                source_count INTEGER DEFAULT 0,
                cache_hit BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMPTZ DEFAULT NOW()
            )
        SQL);

        DB::statement('CREATE INDEX IF NOT EXISTS idx_ai_request_logs_created ON ai.ai_request_logs (created_at DESC)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_ai_request_logs_mode ON ai.ai_request_logs (mode)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_ai_request_logs_success ON ai.ai_request_logs (success)');

        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS ai.ai_feature_snapshots (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                snapshot_time TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                description TEXT,
                metadata JSONB,
                feature_type VARCHAR(255),
                snapshot_id BIGINT,
                feature_name VARCHAR(255),
                mean_value DOUBLE PRECISION DEFAULT 0,
                variance DOUBLE PRECISION DEFAULT 0,
                record_count INTEGER DEFAULT 0,
                created_at TIMESTAMPTZ DEFAULT NOW(),
                updated_at TIMESTAMPTZ DEFAULT NOW()
            )
        SQL);

        DB::statement('CREATE INDEX IF NOT EXISTS idx_ai_feature_snapshots_snapshot_id ON ai.ai_feature_snapshots (snapshot_id)');

        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS ai.ai_settings (
                key VARCHAR(150) PRIMARY KEY,
                value JSONB,
                description TEXT,
                created_at TIMESTAMPTZ DEFAULT NOW(),
                updated_at TIMESTAMPTZ DEFAULT NOW()
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS ai.ai_manual_documents (
                id BIGSERIAL PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                collection_name VARCHAR(150) NOT NULL,
                content TEXT NOT NULL,
                required_permission VARCHAR(150),
                metadata JSONB,
                external_source_url TEXT,
                indexing_status VARCHAR(50) DEFAULT 'pending',
                last_indexed_at TIMESTAMPTZ,
                expires_at TIMESTAMPTZ,
                created_by VARCHAR(128),
                created_at TIMESTAMPTZ DEFAULT NOW(),
                updated_at TIMESTAMPTZ DEFAULT NOW()
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS ai.ai_sample_features (
                id BIGSERIAL PRIMARY KEY,
                sample_id BIGINT NOT NULL,
                snapshot_id UUID NOT NULL REFERENCES ai.ai_feature_snapshots(id) ON DELETE CASCADE,
                tat_days DOUBLE PRECISION,
                stage_count INTEGER DEFAULT 0,
                priority_score INTEGER DEFAULT 0,
                rework_flag BOOLEAN DEFAULT FALSE,
                is_qc_batch BOOLEAN DEFAULT FALSE,
                processing_delay_days DOUBLE PRECISION,
                approval_delay_days DOUBLE PRECISION,
                customer_id BIGINT,
                feature_version VARCHAR(32) DEFAULT 'v1.0',
                created_at TIMESTAMPTZ DEFAULT NOW(),
                updated_at TIMESTAMPTZ DEFAULT NOW(),
                UNIQUE (sample_id, snapshot_id)
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS ai.ai_equipment_features (
                id BIGSERIAL PRIMARY KEY,
                equipment_id BIGINT NOT NULL,
                snapshot_id UUID NOT NULL REFERENCES ai.ai_feature_snapshots(id) ON DELETE CASCADE,
                days_since_service DOUBLE PRECISION,
                days_until_due DOUBLE PRECISION,
                failure_rate DOUBLE PRECISION DEFAULT 0,
                usage_frequency DOUBLE PRECISION DEFAULT 0,
                maintenance_count INTEGER DEFAULT 0,
                calibration_count INTEGER DEFAULT 0,
                verification_count INTEGER DEFAULT 0,
                risk_score DOUBLE PRECISION DEFAULT 0,
                feature_version VARCHAR(32) DEFAULT 'v1.0',
                created_at TIMESTAMPTZ DEFAULT NOW(),
                updated_at TIMESTAMPTZ DEFAULT NOW(),
                UNIQUE (equipment_id, snapshot_id)
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS ai.ai_qc_features (
                id BIGSERIAL PRIMARY KEY,
                qc_result_id BIGINT NOT NULL,
                snapshot_id UUID NOT NULL REFERENCES ai.ai_feature_snapshots(id) ON DELETE CASCADE,
                cv_percent DOUBLE PRECISION,
                z_score DOUBLE PRECISION,
                moving_avg_10 DOUBLE PRECISION,
                moving_std_10 DOUBLE PRECISION,
                trend_direction VARCHAR(32),
                within_control_limits BOOLEAN DEFAULT TRUE,
                westgard_violation BOOLEAN DEFAULT FALSE,
                outlier_flag BOOLEAN DEFAULT FALSE,
                analyte_id BIGINT,
                qc_level VARCHAR(100),
                feature_version VARCHAR(32) DEFAULT 'v1.0',
                created_at TIMESTAMPTZ DEFAULT NOW(),
                updated_at TIMESTAMPTZ DEFAULT NOW(),
                UNIQUE (qc_result_id, snapshot_id)
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS ai.ai_prediction_runs (
                id BIGSERIAL PRIMARY KEY,
                model_id BIGINT,
                model_type VARCHAR(100),
                entity_type VARCHAR(255),
                entity_id VARCHAR(150),
                predicted_value DOUBLE PRECISION,
                confidence DOUBLE PRECISION,
                features JSONB,
                prediction_output JSONB,
                created_at TIMESTAMPTZ DEFAULT NOW(),
                updated_at TIMESTAMPTZ DEFAULT NOW()
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS ai.ai_predictions (
                id BIGSERIAL PRIMARY KEY,
                entity_type VARCHAR(255) NOT NULL,
                entity_id VARCHAR(150) NOT NULL,
                model_type VARCHAR(100) NOT NULL,
                prediction JSONB,
                confidence DOUBLE PRECISION,
                is_valid BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMPTZ DEFAULT NOW(),
                updated_at TIMESTAMPTZ DEFAULT NOW()
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS ai.ai_feedback (
                id BIGSERIAL PRIMARY KEY,
                prediction_id BIGINT REFERENCES ai.ai_prediction_runs(id) ON DELETE SET NULL,
                actual_value DOUBLE PRECISION,
                prediction_error DOUBLE PRECISION,
                absolute_error DOUBLE PRECISION,
                user_feedback TEXT,
                feedback_type VARCHAR(50),
                user_id VARCHAR(128),
                created_at TIMESTAMPTZ DEFAULT NOW(),
                updated_at TIMESTAMPTZ DEFAULT NOW()
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS ai.ai_action_feedback (
                id BIGSERIAL PRIMARY KEY,
                trace_id VARCHAR(100),
                decision_id VARCHAR(100),
                entity_id VARCHAR(150),
                module VARCHAR(100),
                suggested_action TEXT,
                features_snapshot JSONB,
                model_version VARCHAR(100),
                decision_output JSONB,
                user_action VARCHAR(100),
                outcome_result VARCHAR(100),
                user_feedback_text TEXT,
                created_at TIMESTAMPTZ DEFAULT NOW(),
                updated_at TIMESTAMPTZ DEFAULT NOW()
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS ai.ai_model_registry (
                id BIGSERIAL PRIMARY KEY,
                model_name VARCHAR(255) NOT NULL,
                model_type VARCHAR(100) NOT NULL,
                version VARCHAR(100) NOT NULL DEFAULT '1.0.0',
                framework VARCHAR(100),
                artifact_path TEXT,
                feature_snapshot_id UUID,
                training_rows INTEGER DEFAULT 0,
                training_duration_seconds DOUBLE PRECISION,
                hyperparameters JSONB,
                metrics JSONB,
                is_active BOOLEAN DEFAULT FALSE,
                is_deprecated BOOLEAN DEFAULT FALSE,
                deployed_at TIMESTAMPTZ,
                created_at TIMESTAMPTZ DEFAULT NOW(),
                updated_at TIMESTAMPTZ DEFAULT NOW(),
                UNIQUE (model_type, version)
            )
        SQL);

        DB::statement('CREATE INDEX IF NOT EXISTS idx_ai_model_registry_model_type ON ai.ai_model_registry (model_type)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_ai_model_registry_is_active ON ai.ai_model_registry (is_active)');

        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS ai.ai_index_state (
                table_key VARCHAR(150) PRIMARY KEY,
                last_indexed_at TIMESTAMPTZ,
                last_source_watermark BIGINT DEFAULT 0,
                chunks_produced_last INTEGER DEFAULT 0,
                embeddings_produced_last INTEGER DEFAULT 0,
                index_status VARCHAR(50),
                index_error TEXT,
                updated_at TIMESTAMPTZ DEFAULT NOW()
            )
        SQL);

        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS ai.ai_model_baselines (
                id BIGSERIAL PRIMARY KEY,
                model_type VARCHAR(100) NOT NULL,
                feature_name VARCHAR(150) NOT NULL,
                baseline_values JSONB,
                statistics JSONB,
                created_at TIMESTAMPTZ DEFAULT NOW(),
                updated_at TIMESTAMPTZ DEFAULT NOW(),
                UNIQUE (model_type, feature_name)
            )
        SQL);

        $vectorTypeAvailable = DB::selectOne("SELECT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'vector') AS exists")->exists ?? false;
        if ($vectorTypeAvailable) {
            DB::statement(<<<'SQL'
                CREATE TABLE IF NOT EXISTS ai.ai_knowledge_chunks (
                    id BIGSERIAL PRIMARY KEY,
                    chunk_id VARCHAR(255) NOT NULL UNIQUE,
                    collection_name VARCHAR(150) NOT NULL,
                    entity_type VARCHAR(255),
                    entity_id VARCHAR(150),
                    content TEXT NOT NULL,
                    embedding vector(1536),
                    metadata JSONB,
                    required_permission VARCHAR(150),
                    company_id VARCHAR(128),
                    expires_at TIMESTAMPTZ,
                    source_lineage_url TEXT,
                    tsvector_content TSVECTOR GENERATED ALWAYS AS (to_tsvector('english', COALESCE(content, ''))) STORED,
                    created_at TIMESTAMPTZ DEFAULT NOW(),
                    updated_at TIMESTAMPTZ DEFAULT NOW()
                )
            SQL);

            DB::statement('CREATE INDEX IF NOT EXISTS idx_ai_knowledge_chunks_collection ON ai.ai_knowledge_chunks (collection_name)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_ai_knowledge_chunks_entity ON ai.ai_knowledge_chunks (entity_type, entity_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_ai_knowledge_chunks_permission ON ai.ai_knowledge_chunks (required_permission)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_ai_knowledge_chunks_tsv ON ai.ai_knowledge_chunks USING GIN (tsvector_content)');
        }
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS ai.ai_knowledge_chunks CASCADE');
        DB::statement('DROP TABLE IF EXISTS ai.ai_model_baselines CASCADE');
        DB::statement('DROP TABLE IF EXISTS ai.ai_index_state CASCADE');
        DB::statement('DROP TABLE IF EXISTS ai.ai_action_feedback CASCADE');
        DB::statement('DROP TABLE IF EXISTS ai.ai_feedback CASCADE');
        DB::statement('DROP TABLE IF EXISTS ai.ai_predictions CASCADE');
        DB::statement('DROP TABLE IF EXISTS ai.ai_prediction_runs CASCADE');
        DB::statement('DROP TABLE IF EXISTS ai.ai_qc_features CASCADE');
        DB::statement('DROP TABLE IF EXISTS ai.ai_equipment_features CASCADE');
        DB::statement('DROP TABLE IF EXISTS ai.ai_sample_features CASCADE');
        DB::statement('DROP TABLE IF EXISTS ai.ai_manual_documents CASCADE');
        DB::statement('DROP TABLE IF EXISTS ai.ai_settings CASCADE');
        DB::statement('DROP TABLE IF EXISTS ai.ai_request_logs CASCADE');
    }
};

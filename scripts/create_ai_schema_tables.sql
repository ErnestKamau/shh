-- ============================================================================
-- AI Feature Engineering Schema Tables
-- ============================================================================
-- Creates core AI repository and feature engineering tables
-- Run this against your imara-ai PostgreSQL database after bootstrap_db.sql
-- 
-- Usage:
--   psql -h localhost -U root -d imara_ai < scripts/create_ai_schema_tables.sql
-- ============================================================================

-- Ensure ai schema exists (should already exist from bootstrap_db.sql)
CREATE SCHEMA IF NOT EXISTS ai;

-- ============================================================================
-- 1. FEATURE SNAPSHOTS (Critical for reproducibility)
-- ============================================================================
-- Tracks each feature engineering run with a unique snapshot_id.
-- Enables versioning, A/B testing, and reproducible ML experiments.
-- ============================================================================
CREATE TABLE IF NOT EXISTS ai.ai_feature_snapshots (
    id                  SERIAL PRIMARY KEY,
    snapshot_time       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    description         TEXT,
    metadata            JSONB,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_feature_snapshots_time 
    ON ai.ai_feature_snapshots (snapshot_time DESC);

COMMENT ON TABLE ai.ai_feature_snapshots IS 
    'Tracks feature engineering runs for reproducibility and versioning';
COMMENT ON COLUMN ai.ai_feature_snapshots.snapshot_time IS 
    'When this feature snapshot was created';
COMMENT ON COLUMN ai.ai_feature_snapshots.metadata IS 
    'Optional JSON metadata (e.g., ETL run info, data sources, counts)';


-- ============================================================================
-- 2. SAMPLE FEATURES (TAT prediction, workflow optimization)
-- ============================================================================
-- Engineered features from sample_headers for ML models.
-- Focuses on turnaround time (TAT) prediction and process optimization.
-- ============================================================================
CREATE TABLE IF NOT EXISTS ai.ai_sample_features (
    id                  SERIAL PRIMARY KEY,
    sample_id           BIGINT NOT NULL,
    snapshot_id         INT NOT NULL REFERENCES ai.ai_feature_snapshots(id) ON DELETE CASCADE,
    
    -- Core features
    tat_days            FLOAT,
    stage_count         INT,
    priority_score      INT,
    rework_flag         BOOLEAN DEFAULT FALSE,
    
    -- Additional contextual features
    is_qc_batch         BOOLEAN DEFAULT FALSE,
    processing_delay_days FLOAT,
    approval_delay_days FLOAT,
    customer_id         BIGINT,
    
    -- Metadata
    feature_version     TEXT DEFAULT 'v1.0',
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    -- Ensure no duplicate sample features per snapshot
    CONSTRAINT uq_sample_features_snapshot UNIQUE (sample_id, snapshot_id)
);

CREATE INDEX IF NOT EXISTS idx_sample_features_sample_id 
    ON ai.ai_sample_features (sample_id);
CREATE INDEX IF NOT EXISTS idx_sample_features_snapshot_id 
    ON ai.ai_sample_features (snapshot_id);
CREATE INDEX IF NOT EXISTS idx_sample_features_tat 
    ON ai.ai_sample_features (tat_days) WHERE tat_days IS NOT NULL;
CREATE INDEX IF NOT EXISTS idx_sample_features_priority 
    ON ai.ai_sample_features (priority_score);

COMMENT ON TABLE ai.ai_sample_features IS 
    'Engineered features for sample TAT prediction and workflow analysis';
COMMENT ON COLUMN ai.ai_sample_features.tat_days IS 
    'Turnaround time in days (target variable for ML)';
COMMENT ON COLUMN ai.ai_sample_features.stage_count IS 
    'Encoded workflow stage (categorical → numeric)';
COMMENT ON COLUMN ai.ai_sample_features.priority_score IS 
    'Priority level: 1=low, 2=medium, 3=high';
COMMENT ON COLUMN ai.ai_sample_features.rework_flag IS 
    'True if sample required rework or repeat testing';


-- ============================================================================
-- 3. EQUIPMENT FEATURES (Maintenance prediction, failure risk)
-- ============================================================================
-- Engineered features from equipment_assets and equipment_logs.
-- Enables predictive maintenance and failure risk scoring.
-- ============================================================================
CREATE TABLE IF NOT EXISTS ai.ai_equipment_features (
    id                  SERIAL PRIMARY KEY,
    equipment_id        BIGINT NOT NULL,
    snapshot_id         INT NOT NULL REFERENCES ai.ai_feature_snapshots(id) ON DELETE CASCADE,
    
    -- Core features
    days_since_service  INT,
    days_until_due      INT,
    failure_rate        FLOAT,
    usage_frequency     FLOAT,
    
    -- Extended features
    maintenance_count   INT DEFAULT 0,
    calibration_count   INT DEFAULT 0,
    verification_count  INT DEFAULT 0,
    risk_score          FLOAT,
    
    -- Metadata
    feature_version     TEXT DEFAULT 'v1.0',
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT uq_equipment_features_snapshot UNIQUE (equipment_id, snapshot_id)
);

CREATE INDEX IF NOT EXISTS idx_equipment_features_equipment_id 
    ON ai.ai_equipment_features (equipment_id);
CREATE INDEX IF NOT EXISTS idx_equipment_features_snapshot_id 
    ON ai.ai_equipment_features (snapshot_id);
CREATE INDEX IF NOT EXISTS idx_equipment_features_risk 
    ON ai.ai_equipment_features (risk_score) WHERE risk_score IS NOT NULL;
CREATE INDEX IF NOT EXISTS idx_equipment_features_days_since_service 
    ON ai.ai_equipment_features (days_since_service);

COMMENT ON TABLE ai.ai_equipment_features IS 
    'Engineered features for equipment maintenance prediction and risk scoring';
COMMENT ON COLUMN ai.ai_equipment_features.days_since_service IS 
    'Days elapsed since last maintenance/calibration';
COMMENT ON COLUMN ai.ai_equipment_features.failure_rate IS 
    'Ratio of failure events to total usage events';
COMMENT ON COLUMN ai.ai_equipment_features.usage_frequency IS 
    'Average usage events per day';


-- ============================================================================
-- 4. QC FEATURES (Quality control analytics, anomaly detection)
-- ============================================================================
-- Engineered features from qc_processed_results.
-- Supports QC trend analysis and anomaly detection.
-- ============================================================================
CREATE TABLE IF NOT EXISTS ai.ai_qc_features (
    id                  SERIAL PRIMARY KEY,
    qc_result_id        BIGINT NOT NULL,
    snapshot_id         INT NOT NULL REFERENCES ai.ai_feature_snapshots(id) ON DELETE CASCADE,
    
    -- Statistical features
    cv_percent          FLOAT,
    z_score             FLOAT,
    moving_avg_10       FLOAT,
    moving_std_10       FLOAT,
    trend_direction     TEXT, -- 'up', 'down', 'stable'
    
    -- Quality indicators
    within_control_limits BOOLEAN DEFAULT TRUE,
    westgard_violation  BOOLEAN DEFAULT FALSE,
    outlier_flag        BOOLEAN DEFAULT FALSE,
    
    -- Metadata
    analyte_id          BIGINT,
    qc_level            TEXT,
    feature_version     TEXT DEFAULT 'v1.0',
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT uq_qc_features_snapshot UNIQUE (qc_result_id, snapshot_id)
);

CREATE INDEX IF NOT EXISTS idx_qc_features_qc_result_id 
    ON ai.ai_qc_features (qc_result_id);
CREATE INDEX IF NOT EXISTS idx_qc_features_snapshot_id 
    ON ai.ai_qc_features (snapshot_id);
CREATE INDEX IF NOT EXISTS idx_qc_features_analyte 
    ON ai.ai_qc_features (analyte_id);
CREATE INDEX IF NOT EXISTS idx_qc_features_outliers 
    ON ai.ai_qc_features (outlier_flag) WHERE outlier_flag = TRUE;

COMMENT ON TABLE ai.ai_qc_features IS 
    'Engineered features for QC trend analysis and anomaly detection';
COMMENT ON COLUMN ai.ai_qc_features.cv_percent IS 
    'Coefficient of variation (%)';
COMMENT ON COLUMN ai.ai_qc_features.z_score IS 
    'Standard score relative to historical mean';
COMMENT ON COLUMN ai.ai_qc_features.westgard_violation IS 
    'True if Westgard rules are violated';


-- ============================================================================
-- 5. PREDICTION RUNS (Model inference tracking)
-- ============================================================================
-- Tracks ML model predictions for auditing and performance monitoring.
-- Links predictions back to feature snapshots for reproducibility.
-- ============================================================================
CREATE TABLE IF NOT EXISTS ai.ai_prediction_runs (
    id                  SERIAL PRIMARY KEY,
    model_name          TEXT NOT NULL,
    model_version       TEXT NOT NULL,
    snapshot_id         INT REFERENCES ai.ai_feature_snapshots(id) ON DELETE SET NULL,
    
    -- Prediction details
    prediction_type     TEXT, -- 'tat', 'equipment_failure', 'qc_anomaly'
    entity_id           BIGINT, -- ID of the entity being predicted (sample, equipment, etc.)
    predicted_value     FLOAT,
    confidence_score    FLOAT,
    
    -- Model metadata
    model_path          TEXT,
    hyperparameters     JSONB,
    
    -- Timing
    run_time            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    inference_duration_ms INT,
    
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_prediction_runs_model 
    ON ai.ai_prediction_runs (model_name, model_version);
CREATE INDEX IF NOT EXISTS idx_prediction_runs_snapshot 
    ON ai.ai_prediction_runs (snapshot_id);
CREATE INDEX IF NOT EXISTS idx_prediction_runs_type 
    ON ai.ai_prediction_runs (prediction_type);
CREATE INDEX IF NOT EXISTS idx_prediction_runs_entity 
    ON ai.ai_prediction_runs (entity_id);
CREATE INDEX IF NOT EXISTS idx_prediction_runs_time 
    ON ai.ai_prediction_runs (run_time DESC);

COMMENT ON TABLE ai.ai_prediction_runs IS 
    'Tracks ML model inference runs for auditing and monitoring';
COMMENT ON COLUMN ai.ai_prediction_runs.model_name IS 
    'Name of the ML model (e.g., "tat_predictor", "equipment_risk")';
COMMENT ON COLUMN ai.ai_prediction_runs.snapshot_id IS 
    'Links prediction to feature snapshot for reproducibility';
COMMENT ON COLUMN ai.ai_prediction_runs.confidence_score IS 
    'Model confidence (0.0 to 1.0)';


-- ============================================================================
-- 6. FEEDBACK LOOP (Continuous improvement)
-- ============================================================================
-- Captures actual outcomes vs predictions for model retraining.
-- Enables continuous learning and model performance monitoring.
-- ============================================================================
CREATE TABLE IF NOT EXISTS ai.ai_feedback (
    id                  SERIAL PRIMARY KEY,
    prediction_id       INT REFERENCES ai.ai_prediction_runs(id) ON DELETE CASCADE,
    
    -- Actual outcome
    actual_value        FLOAT,
    prediction_error    FLOAT, -- actual - predicted
    absolute_error      FLOAT, -- |actual - predicted|
    
    -- User feedback
    user_feedback       TEXT,
    feedback_type       TEXT, -- 'correct', 'incorrect', 'comment'
    user_id             BIGINT,
    
    -- Metadata
    collected_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_feedback_prediction_id 
    ON ai.ai_feedback (prediction_id);
CREATE INDEX IF NOT EXISTS idx_feedback_type 
    ON ai.ai_feedback (feedback_type);
CREATE INDEX IF NOT EXISTS idx_feedback_collected_at 
    ON ai.ai_feedback (collected_at DESC);

COMMENT ON TABLE ai.ai_feedback IS 
    'Captures actual outcomes for model performance monitoring and retraining';
COMMENT ON COLUMN ai.ai_feedback.prediction_error IS 
    'Difference between actual and predicted value (signed)';
COMMENT ON COLUMN ai.ai_feedback.feedback_type IS 
    'Type of feedback: correct, incorrect, or general comment';


-- ============================================================================
-- 7. MODEL REGISTRY
-- ============================================================================
-- Tracks every trained model version (TAT prediction, equipment maintenance,
-- QC anomaly detection). Supports A/B testing and safe rollback via is_active.
-- ============================================================================
CREATE TABLE IF NOT EXISTS ai.ai_model_registry (
    id                          SERIAL PRIMARY KEY,

    -- Identity
    model_name                  TEXT NOT NULL,                      -- e.g. 'tat_prediction_rf'
    model_type                  TEXT NOT NULL,                      -- 'tat_prediction' | 'equipment_maintenance' | 'qc_anomaly'
    version                     TEXT NOT NULL,                      -- semantic: '1.0.0'
    framework                   TEXT,                               -- 'sklearn' | 'xgboost' | 'pytorch'

    -- Artifact
    artifact_path               TEXT,                               -- absolute FS path to serialized model
    feature_snapshot_id         INT REFERENCES ai.ai_feature_snapshots(id) ON DELETE SET NULL,

    -- Training provenance
    training_rows               INT,
    training_duration_seconds   FLOAT,
    hyperparameters             JSONB,

    -- Performance metrics
    metrics                     JSONB,                              -- {accuracy, f1, rmse, auc, ...}

    -- Lifecycle
    is_active                   BOOLEAN NOT NULL DEFAULT FALSE,     -- only one per model_type should be TRUE
    is_deprecated               BOOLEAN NOT NULL DEFAULT FALSE,
    deployed_at                 TIMESTAMP,
    created_at                  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (model_type, version)
);

CREATE INDEX IF NOT EXISTS idx_model_registry_type
    ON ai.ai_model_registry (model_type);
CREATE INDEX IF NOT EXISTS idx_model_registry_active
    ON ai.ai_model_registry (model_type, is_active)
    WHERE is_active = TRUE;
CREATE INDEX IF NOT EXISTS idx_model_registry_snapshot
    ON ai.ai_model_registry (feature_snapshot_id);

COMMENT ON TABLE ai.ai_model_registry IS
    'Versioned registry for all trained ML models; supports rollback and A/B testing';
COMMENT ON COLUMN ai.ai_model_registry.model_type IS
    'Logical model purpose: tat_prediction, equipment_maintenance, qc_anomaly';
COMMENT ON COLUMN ai.ai_model_registry.is_active IS
    'Exactly one model per model_type should be active for production serving';
COMMENT ON COLUMN ai.ai_model_registry.artifact_path IS
    'Filesystem path to serialised model artifact (.joblib / .pkl)';


-- ============================================================================
-- 8. INFERENCE AUDIT LOG
-- ============================================================================
-- Every prediction call is logged here for traceability, compliance (ISO 17025)
-- and drift detection. Links back to the model that produced the prediction.
-- ============================================================================
CREATE TABLE IF NOT EXISTS ai.ai_inference_audit (
    id                  SERIAL PRIMARY KEY,

    -- Which model was used
    model_registry_id   INT REFERENCES ai.ai_model_registry(id) ON DELETE SET NULL,
    model_name          TEXT NOT NULL,
    model_version       TEXT,

    -- What was scored
    entity_type         TEXT NOT NULL,                              -- 'sample' | 'equipment' | 'qc'
    entity_id           BIGINT,

    -- Input / Output
    input_features      JSONB,                                      -- feature vector sent to the model
    prediction          JSONB,                                      -- raw model output (class / score / probabilities)
    confidence          FLOAT,                                      -- probability or certainty score 0-1

    -- Operational metadata
    latency_ms          INT,                                        -- inference wall-clock time
    request_source      TEXT,                                       -- 'api' | 'scheduler' | 'artisan' | 'celery'
    requested_by        TEXT,                                       -- user or service account

    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_inference_audit_entity
    ON ai.ai_inference_audit (entity_type, entity_id);
CREATE INDEX IF NOT EXISTS idx_inference_audit_model
    ON ai.ai_inference_audit (model_registry_id);
CREATE INDEX IF NOT EXISTS idx_inference_audit_created
    ON ai.ai_inference_audit (created_at DESC);

COMMENT ON TABLE ai.ai_inference_audit IS
    'Immutable audit log of every prediction for compliance and drift monitoring';
COMMENT ON COLUMN ai.ai_inference_audit.entity_type IS
    'Domain entity that was scored: sample, equipment, qc';
COMMENT ON COLUMN ai.ai_inference_audit.input_features IS
    'Snapshot of the feature vector used — enables offline re-scoring';


-- ============================================================================
-- GRANT PERMISSIONS
-- ============================================================================
-- Grant necessary permissions to the AI database user.
-- Adjust username based on your .env configuration (AI_DB_USERNAME).
-- ============================================================================

DO $$
DECLARE
    db_user TEXT := current_user;
BEGIN
    -- Grant schema usage
    EXECUTE format('GRANT USAGE ON SCHEMA ai TO %I', db_user);
    EXECUTE format('GRANT ALL PRIVILEGES ON SCHEMA ai TO %I', db_user);
    
    -- Grant table privileges
    EXECUTE format('GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA ai TO %I', db_user);
    EXECUTE format('GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA ai TO %I', db_user);
    
    -- Grant default privileges for future objects
    EXECUTE format('ALTER DEFAULT PRIVILEGES IN SCHEMA ai GRANT ALL PRIVILEGES ON TABLES TO %I', db_user);
    EXECUTE format('ALTER DEFAULT PRIVILEGES IN SCHEMA ai GRANT ALL PRIVILEGES ON SEQUENCES TO %I', db_user);
    
    RAISE NOTICE 'Permissions granted to user: %', db_user;
END
$$;


-- ============================================================================
-- VERIFICATION QUERY
-- ============================================================================
-- Run this to verify all tables were created successfully:
-- ============================================================================

SELECT 
    schemaname,
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS size
FROM pg_tables
WHERE schemaname = 'ai'
ORDER BY tablename;

-- Expected output: 8 tables
-- - ai_equipment_features
-- - ai_feature_snapshots
-- - ai_feedback
-- - ai_inference_audit
-- - ai_model_registry
-- - ai_prediction_runs
-- - ai_qc_features
-- - ai_sample_features

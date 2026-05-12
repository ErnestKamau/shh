#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [[ -f "$ROOT_DIR/.env" ]]; then
  set -a
  set +u
  source "$ROOT_DIR/.env"
  set -u
  set +a
fi

AI_URL="${AI_SERVICE_URL:-http://127.0.0.1:8081}"
OLLAMA_HOST="${OLLAMA_HOST:-http://127.0.0.1:11434}"
OLLAMA_MODEL="${OLLAMA_MODEL:-qwen2.5:3b}"

require_cmd() {
  if ! command -v "$1" >/dev/null 2>&1; then
    echo "Missing required command: $1"
    exit 1
  fi
}

wait_for_http() {
  local url="$1"
  local label="$2"
  local retries="${3:-20}"
  local sleep_secs="${4:-1}"

  for _ in $(seq 1 "$retries"); do
    if curl -fsS "$url" >/dev/null 2>&1; then
      echo "Ready: $label"
      return 0
    fi
    sleep "$sleep_secs"
  done

  echo "Timed out waiting for: $label ($url)"
  return 1
}

model_provisioning() {
  echo "[Model Provisioning] Installing local LLM model in Ollama"
  require_cmd ollama

  if ollama pull "$OLLAMA_MODEL"; then
    echo "[Model Provisioning] Installed model: $OLLAMA_MODEL"
    return 0
  fi

  if [[ "$OLLAMA_MODEL" != "qwen2.5:3b" ]]; then
    echo "[Model Provisioning] Requested model not available: $OLLAMA_MODEL"
    echo "[Model Provisioning] Falling back to qwen2.5:3b"
    ollama pull qwen2.5:3b
    echo "[Model Provisioning] Installed model: qwen2.5:3b"
    echo "[Model Provisioning] Tip: set OLLAMA_MODEL=qwen2.5:3b in your environment for consistency"
    return 0
  fi

  echo "[Model Provisioning] Could not install model: $OLLAMA_MODEL"
  return 1
}

runtime_bootstrap() {
  echo "[Runtime Bootstrap] Starting Ollama and AI API services"
  require_cmd curl
  require_cmd ollama

  if ! curl -fsS "$OLLAMA_HOST/api/tags" >/dev/null 2>&1; then
    nohup env OLLAMA_HOST="$OLLAMA_HOST" ollama serve >/tmp/polucon-ollama.log 2>&1 &
    wait_for_http "$OLLAMA_HOST/api/tags" "Ollama API"
  else
    echo "[Runtime Bootstrap] Ollama already reachable at $OLLAMA_HOST"
  fi

  if ! curl -fsS "$AI_URL/health/live" >/dev/null 2>&1; then
    if [[ ! -f "$ROOT_DIR/.venv/bin/activate" ]]; then
      echo "Missing virtual environment at $ROOT_DIR/.venv"
      exit 1
    fi

    nohup bash -lc "cd '$ROOT_DIR' && source .venv/bin/activate && uvicorn python.ai_service.main:app --host 127.0.0.1 --port 8081" >/tmp/polucon-ai-service.log 2>&1 &
    wait_for_http "$AI_URL/health/live" "AI API live endpoint"
  else
    echo "[Runtime Bootstrap] AI API already reachable at $AI_URL"
  fi

  echo "[Runtime Bootstrap] Service readiness snapshot"
  curl -fsS "$AI_URL/health/ready" || true
  echo
}

predictive_training() {
  echo "[Predictive Training] Training TAT and Equipment models from FIVET-derived features"
  if [[ ! -f "$ROOT_DIR/.venv/bin/activate" ]]; then
    echo "Missing virtual environment at $ROOT_DIR/.venv"
    exit 1
  fi

  feature_materialization

  local version_tag
  version_tag="local_$(date +%Y%m%d_%H%M%S)"

  if ! bash -lc "cd '$ROOT_DIR' && source .venv/bin/activate && python -m python.ai_service.train_models --target all --version '$version_tag'"; then
    echo "[Predictive Training] Combined training failed, falling back to per-model training"

    bash -lc "cd '$ROOT_DIR' && source .venv/bin/activate && python -m python.ai_service.train_models --target tat --version '$version_tag'"

    if ! bash -lc "cd '$ROOT_DIR' && source .venv/bin/activate && python -m python.ai_service.train_models --target equipment --version '$version_tag'"; then
      echo "[Predictive Training] Equipment model skipped (likely single-class data). TAT model remains active."
    fi
  fi

  echo "[Predictive Training] Completed with version tag: $version_tag"
}

feature_foundation() {
  echo "[Feature Foundation] Ensuring AI feature tables exist"
  require_cmd psql

  local db_host db_port db_name db_user db_password
  db_host="${AI_DB_HOST:-127.0.0.1}"
  db_port="${AI_DB_PORT:-5432}"
  db_name="${AI_DB_DATABASE:-imara_ai}"
  db_user="${AI_DB_USERNAME:-}"
  db_password="${AI_DB_PASSWORD:-}"

  if [[ -z "$db_user" || -z "$db_password" ]]; then
    echo "[Feature Foundation] Missing AI_DB_USERNAME or AI_DB_PASSWORD in environment"
    return 1
  fi

  PGPASSWORD="$db_password" psql -h "$db_host" -p "$db_port" -U "$db_user" -d "$db_name" -f "$ROOT_DIR/scripts/create_ai_schema_tables.sql"

  PGPASSWORD="$db_password" psql -h "$db_host" -p "$db_port" -U "$db_user" -d "$db_name" <<'SQL'
CREATE SCHEMA IF NOT EXISTS reporting;

CREATE TABLE IF NOT EXISTS reporting.sync_runs (
  id BIGSERIAL PRIMARY KEY,
  sync_scope TEXT,
  source_table TEXT,
  target_table TEXT,
  status TEXT,
  rows_synced BIGINT DEFAULT 0,
  rows_failed BIGINT DEFAULT 0,
  rows_quarantined BIGINT DEFAULT 0,
  stage TEXT,
  chunk_count INT DEFAULT 0,
  started_at TIMESTAMPTZ,
  finished_at TIMESTAMPTZ,
  duration_seconds INT,
  error_message TEXT,
  metadata JSONB,
  failed_chunks JSONB,
  created_at TIMESTAMPTZ DEFAULT NOW(),
  updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS reporting.sync_quarantine (
  id BIGSERIAL PRIMARY KEY,
  run_id BIGINT,
  source_table TEXT,
  raw_payload JSONB,
  validation_error TEXT,
  quarantine_reason TEXT,
  created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS reporting.sync_index_state (
  table_key TEXT PRIMARY KEY,
  last_sync_at TIMESTAMPTZ,
  rows_synced_last_run BIGINT,
  sync_status TEXT,
  sync_error TEXT,
  last_indexed_at TIMESTAMPTZ,
  last_source_watermark BIGINT,
  chunks_produced_last BIGINT,
  embeddings_produced_last BIGINT,
  index_status TEXT,
  index_error TEXT,
  updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS reporting.sample_headers (
  source_id BIGINT PRIMARY KEY,
  batch_code TEXT,
  status TEXT,
  crm_customer_id BIGINT,
  verify_user_id BIGINT,
  approve_user_id BIGINT,
  created_by BIGINT,
  is_qc_batch BOOLEAN,
  isactive BOOLEAN,
  processing_date TIMESTAMPTZ,
  approval_date_at TIMESTAMPTZ,
  approval_date_raw TEXT,
  sample_tracking_stage TEXT,
  source_created_at TIMESTAMPTZ,
  source_updated_at TIMESTAMPTZ,
  synced_at TIMESTAMPTZ,
  payload JSONB
);

CREATE TABLE IF NOT EXISTS reporting.equipment_assets (
  source_id BIGINT PRIMARY KEY,
  name TEXT,
  equipment_number TEXT,
  status TEXT,
  assigned_department TEXT,
  assigned_employee_id BIGINT,
  date_purchased TEXT,
  maintainance_days BIGINT,
  maintainance_notification_in_days BIGINT,
  calibration_days BIGINT,
  calibration_notification_in_days BIGINT,
  verification_days BIGINT,
  warranty_date TIMESTAMPTZ,
  is_disposal BOOLEAN,
  active BOOLEAN,
  asset_type_id BIGINT,
  asset_location_id BIGINT,
  source_created_at TIMESTAMPTZ,
  source_updated_at TIMESTAMPTZ,
  synced_at TIMESTAMPTZ,
  payload JSONB
);

CREATE TABLE IF NOT EXISTS reporting.equipment_logs (
  id BIGSERIAL PRIMARY KEY,
  source_table TEXT,
  source_id BIGINT,
  equipment_id BIGINT,
  event_type TEXT,
  event_date TIMESTAMPTZ,
  event_date_raw TEXT,
  service_provider TEXT,
  supplier_id BIGINT,
  performed_by BIGINT,
  edited_by BIGINT,
  reference_number TEXT,
  notes TEXT,
  source_created_at TIMESTAMPTZ,
  source_updated_at TIMESTAMPTZ,
  synced_at TIMESTAMPTZ,
  payload JSONB,
  UNIQUE (source_table, source_id)
);

CREATE TABLE IF NOT EXISTS reporting.tat_captured (
  source_id BIGINT PRIMARY KEY,
  captured_result_id BIGINT,
  analysis_type_id TEXT,
  analyte_id BIGINT,
  sample_type_id BIGINT,
  sample_detail_id BIGINT,
  analyst_id BIGINT,
  sample_header_id BIGINT,
  result_value TEXT,
  tat_overdue_days DOUBLE PRECISION,
  tat_date TIMESTAMPTZ,
  finished_date TIMESTAMPTZ,
  is_complete BOOLEAN,
  tat_remark TEXT,
  source_created_at TIMESTAMPTZ,
  source_updated_at TIMESTAMPTZ,
  synced_at TIMESTAMPTZ,
  payload JSONB
);
SQL

  echo "[Feature Foundation] AI schema table check complete"
}

feature_materialization() {
  echo "[Feature Materialization] Building fresh sample/equipment feature snapshot"
  if [[ ! -f "$ROOT_DIR/.venv/bin/activate" ]]; then
    echo "Missing virtual environment at $ROOT_DIR/.venv"
    exit 1
  fi

  feature_foundation
  reporting_sync
  bash -lc "cd '$ROOT_DIR' && source .venv/bin/activate && python -m python.ai_service.build_feature_snapshot --json"
  echo "[Feature Materialization] Completed"
}

reporting_sync() {
  echo "[Reporting Sync] Populating required reporting tables from FIVET source"
  if [[ ! -f "$ROOT_DIR/.venv/bin/activate" ]]; then
    echo "Missing virtual environment at $ROOT_DIR/.venv"
    exit 1
  fi

  bash -lc "cd '$ROOT_DIR' && source .venv/bin/activate && python -m python.ai_service.sync_reporting_seed --json"
  echo "[Reporting Sync] Completed"
}

chat_validation() {
  echo "[Chat Validation] Running AI chat smoke test"
  require_cmd curl

  local payload
  payload='{"messages":[{"role":"user","content":"How do I monitor lab sample turnaround time in this LIMS system?"}],"company_id":1,"session_id":"local-validation-001"}'

  curl -fsS -X POST "$AI_URL/v1/chat" \
    -H "Content-Type: application/json" \
    -d "$payload"
  echo
}

usage() {
  cat <<'EOF'
Usage: ./scripts/ai_local_flow.sh <process>

Processes:
  model-provisioning   Install the Ollama model used by chat
  runtime-bootstrap    Start and validate Ollama + AI API runtime
  feature-foundation   Ensure AI feature-engineering tables exist in PostgreSQL
  reporting-sync       Sync reporting source tables required by feature engineering
  feature-materialization  Build a new feature snapshot from reporting data
  predictive-training  Train TAT and equipment models from feature views
  chat-validation      Execute one end-to-end chat request
  full-validation      Run all processes in sequence
EOF
}

main() {
  local process="${1:-}"

  case "$process" in
    model-provisioning)
      model_provisioning
      ;;
    runtime-bootstrap)
      runtime_bootstrap
      ;;
    feature-foundation)
      feature_foundation
      ;;
    feature-materialization)
      feature_materialization
      ;;
    reporting-sync)
      reporting_sync
      ;;
    predictive-training)
      predictive_training
      ;;
    chat-validation)
      chat_validation
      ;;
    full-validation)
      model_provisioning
      runtime_bootstrap
      feature_materialization
      predictive_training
      chat_validation
      ;;
    *)
      usage
      exit 1
      ;;
  esac
}

main "$@"

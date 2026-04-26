# Imara AI — Operations & Admin Guide

**Version:** 2026-04-11  
**Audience:** DevOps, System Administrators, Backend Engineers  
**Goal:** Maintain, monitor, and troubleshoot the AI system in production

---

## 0) Latest Implementation Delta (2026-04-11)

This guide now includes operations support for the following production-hardening updates:

- **Metrics export enabled**
  - Laravel exposes Prometheus format at `GET /api/ai/metrics`
  - FastAPI exposes Prometheus format at `GET /metrics`
- **Observability stack deployed**
  - Docker services for `prometheus`, `loki`, `promtail`, and `grafana`
  - Config files under `config/observability/`
- **Automated backup verification**
  - `scripts/backup_verify.sh` creates compressed dumps, verifies with `gzip -t`, and prunes old backups
- **Audit privacy hardening**
  - Feedback actor IDs are persisted using HMAC-SHA256 hash instead of plaintext identifiers
- **Drift governance enhancement**
  - Added Evidently supplemental drift detection flow in Celery task pipeline
  - Auto-retrain trigger path available when drift is confirmed

---

## Table of Contents

1. Service Architecture & Startup
2. Health Checks & Monitoring
3. Database Setup & Migrations
4. Configuration Management
5. Common Issues & Fixes
6. Performance Tuning
7. Backup & Recovery
8. Security Considerations

---

## 1) Service Architecture & Startup

### 1.1 Service Topology

```
┌─────────────────────────────────────────────────────┐
│                  USER BROWSER                        │
└──────────────────────┬──────────────────────────────┘
                       │ HTTP/HTTPS
         ┌─────────────┴─────────────┐
         ▼                           ▼
    ┌─────────────┐            ┌──────────────┐
    │ Laravel     │            │  Browser     │
    │ Port 8000   │◄──────────►│  Storage     │
    └──────┬──────┘            └──────────────┘
           │
           ├─────────────┬──────────────┬──────────────┐
           ▼             ▼              ▼              ▼
        ┌─────┐   ┌──────────┐   ┌──────────┐   ┌────────┐
        │MySQL│   │PostgreSQL│   │ Redis    │   │FastAPI │
        │ App │   │ AI Repo  │   │(optional)│   │ 8001   │
        └─────┘   └──────────┘   └──────────┘   └────────┘
           │                                         │
           │◄────────────────────────────────────────┤
           │     Internal API calls                  │
           │                                         │
        ┌──────────────────────────────┐            │
        │   FastAPI Feature/RAG         │            │
        │   Port 8002                   │            │
        │   (Optional secondary service)│            │
        └──────────────────────────────┘            │
                   ▲                                 │
                   │                                 │
              ┌────┴────────────────────────┐       │
              ▼                             ▼       ▼
          ┌──────────┐              ┌──────────────┐
          │  Ollama  │              │  Celery      │
          │ 11434    │              │  Worker +    │
          │ (LLM)    │              │  Beat        │
          └──────────┘              │(scheduling)  │
                                    └──────────────┘
```

### 1.2 Startup Order (Recommended)

```bash
# Terminal 1: PostgreSQL & MySQL (if running locally)
# Ensure PostgreSQL and MySQL are running
sudo systemctl start postgresql mysql  # or docker containers

# Terminal 2: Ollama LLM runtime
ollama serve

# Terminal 3: Industrialized Unified AI Service (FastAPI)
cd /home/andy/Desktop/Projects/nuvemite/polucon
source .venv/bin/activate
bash scripts/run_inference.sh

# Terminal 4: Laravel Web Server
cd /home/andy/Desktop/Projects/nuvemite/polucon
php artisan serve --host=127.0.0.1 --port=8000

# Terminal 5: Celery Worker
cd /home/andy/Desktop/Projects/nuvemite/polucon
bash scripts/run_worker.sh

# Terminal 6: Celery Beat Scheduler (CRITICAL for analytical sync)
cd /home/andy/Desktop/Projects/nuvemite/polucon
bash scripts/run_beat.sh
```

### 1.3 Verify All Services are Running

```bash
# Quick health check script
curl -s http://127.0.0.1:8081/health | jq .
# Expected: {"status":"online","timestamp":"..."}

curl -s http://127.0.0.1:8000/health
# Expected: 200 OK (or redirect to login)

curl -s http://localhost:11434/api/tags | jq '.models[0].name'
# Expected: "qwen2" or your configured model
```

---

## 2) Health Checks & Monitoring

### 2.1 FastAPI Endpoint Health

```bash
# Overall system health
curl -s http://127.0.0.1:8081/health | jq .

# Model readiness
curl -s http://127.0.0.1:8081/v1/intents | jq '.intents | length'
# Expected: > 0

# Database connectivity (implicit in first prediction)
curl -s -X POST http://127.0.0.1:8081/v1/predict/tat \
  -H "Content-Type: application/json" \
  -d '{"entity_id":1}' | jq '.confidence'
# Expected: 0.0-1.0
```

### 2.2 Laravel Health

```bash
# App is running
curl -I http://127.0.0.1:8000 2>/dev/null | head -1
# Expected: HTTP/1.1 200 OK or 302 Found (redirect to login)

# Database connectivity
php artisan tinker --execute="echo DB::connection('mysql')->select('SELECT 1')[0]->1;"
# Expected: 1

# PostgreSQL AI repo connectivity
php artisan tinker --execute="echo DB::connection('pgsql_ai')->select('SELECT 1')[0]->1;"
# Expected: 1
```

### 2.3 Database Schema Checks

```bash
# PostgreSQL AI tables exist
psql -U $AI_DB_USER -d $AI_DB_DATABASE -c "
  SELECT table_name 
  FROM information_schema.tables 
  WHERE table_schema='ai' 
  ORDER BY table_name;
"
# Expected: ai_predictions, ai_rag_traces, ai_manual_documents, etc.

# MySQL tables exist
mysql -u $DB_USER -p$DB_PASSWORD $DB_DATABASE -e "
  SHOW TABLES LIKE 'ai_%';
"
# Expected: ai_conversations, ai_messages, ai_chat_attachments, etc.
```

### 2.4 Queue Monitoring (if using Celery)

```bash
# Check pending tasks
celery -A python.celery_config.celery_app inspect active
# Expected: No hung tasks or empty dict if idle

# Check scheduled tasks
celery -A python.celery_config.celery_app inspect scheduled
```

### 2.5 Log Monitoring

```bash
# Laravel logs (real-time)
tail -f storage/logs/laravel-$(date +'%Y-%m-%d').log | grep -E 'ERROR|WARNING'

# Application error channel
tail -f storage/logs/prompt-injection-$(date +'%Y-%m-%d').log

# FastAPI logs (STDOUT from terminal 3)
# Look for ERROR or exceptions

# Celery logs
tail -f celery-worker.log 2>/dev/null || echo "No worker log"
```

---

## 3) Database Setup & Migrations

### 3.1 Initial Setup (One-time)

```bash
# Create PostgreSQL AI database
createdb -U postgres $AI_DB_DATABASE

# Create MySQL app database
mysql -u root -p -e "
  CREATE DATABASE IF NOT EXISTS $DB_DATABASE;
  GRANT ALL ON $DB_DATABASE.* TO '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
  FLUSH PRIVILEGES;
"

# Run all Laravel migrations
cd /home/zippy/Documents/polucon
php artisan migrate --database=mysql
php artisan migrate --database=pgsql_ai
```

### 3.2 Adding API Token Support (Already Done)

The migration `2026_04_10_190928_add_api_token_to_users_table` has been applied.

To verify:

```bash
mysql -u $DB_USER -p$DB_PASSWORD $DB_DATABASE -e "
  DESCRIBE users;
" | grep -i token
# Expected: api_token column present
```

### 3.3 Applying New AI Migrations

When rolling out new features:

```bash
# List pending migrations
php artisan migrate:status --database=pgsql_ai

# Apply pending migrations only
php artisan migrate --database=pgsql_ai

# Rollback last batch if needed
php artisan migrate:rollback --database=pgsql_ai
```

### 3.4 Schema Consistency Check

```bash
# Ensure AI schema is fully deployed
php artisan tinker --execute="
require_once 'vendor/autoload.php';
\$schema = include 'database/schema/mysql-ai.sql';
echo 'AI schema version: ' . (new \App\Services\AI\SchemaVersionService())->getVersion();
"
```

---

## 4) Configuration Management

### 4.1 Critical Environment Variables

```bash
# .env file checklist
grep -E '^(APP_|DB_|AI_|QUEUE_|REDIS_)' .env | sort

# Essential AI variables
APP_ENV=production          # or development
DB_HOST=127.0.0.1
DB_DATABASE=polucon
AI_DB_HOST=127.0.0.1
AI_DB_PORT=5432
AI_DB_DATABASE=fivet_imara_ai
AI_DB_SCHEMA=ai
AI_API_URL=http://127.0.0.1:8081
AI_INFERENCE_URL=http://127.0.0.1:8081
AI_ORCHESTRATION_MODE=balanced
QUEUE_CONNECTION=redis       # or sync, database
REDIS_HOST=127.0.0.1
```

### 4.2 Config Caching (Production)

```bash
# Cache config for performance
php artisan config:cache

# Verify caching is active
php artisan config:show app.env
# If in production/cached mode, will use cached version

# Clear cache when updating .env
php artisan config:clear
```

### 4.3 Enabling Feature Flags

```php
// In config/ai.php
'rag' => [
    'enable_vector_search' => env('AI_RAG_VECTOR', true),
    'enable_keyword_search' => env('AI_RAG_KEYWORD', true),
    'enable_reranking' => env('AI_RAG_RERANK', false),  // CPU intensive
    'rerank_top_k' => 20,
    'confidence_threshold' => 0.50,
    'top_k' => 10,
],
```

To enable from command line:

```bash
# Add to .env
echo "AI_RAG_RERANK=true" >> .env
php artisan config:clear
```

---

## 5) Common Issues & Fixes

### Issue: "Connection refused" on port 8081

**Diagnosis:**
```bash
ss -tlnp | grep 8081
# Empty output = not running
```

**Fix:**
```bash
bash scripts/run_inference.sh
```

---

### Issue: PostgreSQL Permission Denied on `ai` schema

**Diagnosis:**
```bash
psql -U $AI_DB_USER -d $AI_DB_DATABASE -c "SELECT 1 FROM ai.ai_predictions LIMIT 1;"
# ERROR: permission denied for schema ai
```

**Fix:**
```bash
# Grant schema permissions
psql -U postgres $AI_DB_DATABASE -c "
  GRANT USAGE ON SCHEMA ai TO $AI_DB_USER;
  GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA ai TO $AI_DB_USER;
  GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA ai TO $AI_DB_USER;
"
```

---

### Issue: Predictions returning "degraded_mode: true"

**Diagnosis:**
```bash
# Check if feature snapshots exist
php artisan tinker --execute="
echo \App\Models\AI\AiSampleFeature::where('sample_id', 1)->first() ? 'exists' : 'missing';
"
```

**Fix:**
- This is **normal** for new entities
- Feature pipeline runs daily at 01:00 UTC
- Or manually trigger:
  ```bash
  php artisan tinker --execute="
  \App\Jobs\AI\FeatureEngineeringJob::dispatch()->onQueue('ai');
  "
  ```

---

### Issue: High memory usage on FastAPI

**Diagnosis:**
```bash
# Check process memory
ps aux | grep 'uvicorn' | grep -v grep
# Look at 5th column (RSS in KB)
```

**Fix:**
- Reduce model batch size in `config/ai.php`
- Disable reranking: `AI_RAG_RERANK=false`
- Restart: `kill -9 <PID>` then restart

---

### Issue: Slow predictions (> 1 second per request)

**Diagnosis:**
```bash
# Check if Ollama is responsive
time curl -s http://localhost:11434/api/tags > /dev/null
# Should complete in < 100ms
```

**Fix:**
- Ensure Ollama is running: `ollama serve`
- Check model is loaded: `ollama list`
- Pre-warm model: `ollama run qwen2.5:3b "test"`
- Monitor system resources: `top` or `htop`

---

## 6) Performance Tuning

### 6.1 Database Query Optimization

```bash
# Enable slow query log (PostgreSQL)
psql -U postgres $AI_DB_DATABASE -c "
  ALTER SYSTEM SET log_min_duration_statement = 1000;  -- 1 second
  SELECT pg_reload_conf();
"

# Check slow queries
tail -f /var/log/postgresql/postgresql.log | grep 'duration:'

# Index key columns
psql -U $AI_DB_USER -d $AI_DB_DATABASE -c "
  CREATE INDEX idx_predictions_entity ON ai.ai_predictions(entity_id, model_type);
  CREATE INDEX idx_rag_traces_query ON ai.ai_rag_traces(query_id);
"
```

### 6.2 FastAPI Concurrency

```python
# In python/ai_service/main.py
app = FastAPI(
    title="Imara AI",
    # Tune for your server capacity
    max_workers=4,  # Increase if 8+ CPU cores
)

# Or via environment
export UVICORN_WORKERS=4  # Default is 1 for development
uvicorn python.ai_service.main:app --workers $UVICORN_WORKERS
```

### 6.3 Redis Caching (if enabled)

```bash
# Monitor cache hits/misses
redis-cli INFO stats | grep total_commands_processed

# Clear cache if needed
redis-cli FLUSHALL
# WARNING: This clears all app caches, not just AI

# Or selectively
redis-cli DEL "ai:*"
```

### 6.4 Celery Task Optimization

```python
# In config/celery.py - tune broker pool
celery.conf.broker_pool_limit = 10  # Connections to broker
celery.conf.broker_connection_max_retries = 3
celery.conf.worker_prefetch_multiplier = 1  # Process one task at a time
```

---

## 7) Backup & Recovery

### 7.1 Database Backups

```bash
# Daily PostgreSQL backup
pg_dump -U $AI_DB_USER $AI_DB_DATABASE \
  | gzip > /backups/polucon_ai_$(date +%Y%m%d).sql.gz

# Daily MySQL backup
mysqldump -u $DB_USER -p$DB_PASSWORD $DB_DATABASE \
  | gzip > /backups/polucon_app_$(date +%Y%m%d).sql.gz

# Automate via cron (as root)
0 2 * * * /home/zippy/backup_databases.sh
```

Backup script: `/home/zippy/backup_databases.sh`

```bash
#!/bin/bash
BACKUP_DIR="/backups"
DATE=$(date +%Y%m%d_%H%M%S)

pg_dump -U postgres polucon_ai | gzip > $BACKUP_DIR/ai_${DATE}.sql.gz
mysqldump -u root polucon | gzip > $BACKUP_DIR/app_${DATE}.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete

echo "Backup completed at $DATE"
```

### 7.2 Recovery Procedure

```bash
# Restore PostgreSQL from backup
gunzip < /backups/polucon_ai_20260410.sql.gz \
  | psql -U postgres polucon_ai

# Restore MySQL from backup
gunzip < /backups/polucon_app_20260410.sql.gz \
  | mysql -u root polucon
```

### 7.3 Code Backup

```bash
# Daily git commit
cd /home/zippy/Documents/polucon
git add -A
git commit -m "Auto-backup $(date +%Y-%m-%d)"
git push origin main
```

---

## 8) Security Considerations

### 8.1 API Authentication

- All API endpoints require Bearer token via `Authorization: Bearer <token>` header
- Tokens stored as plain text in `users.api_token` (guard has `hash=false`)
- For production, consider hashing tokens

```bash
# Generate test token safely
php artisan tinker --execute="
\$u = App\User::first();
\$token = bin2hex(random_bytes(32));
\$u->update(['api_token' => \$token]);
echo 'Token: ' . \$token;
"
```

### 8.2 Database Access Control

```bash
# PostgreSQL AI database should only be accessed by Laravel app
psql -U postgres -c "
  ALTER USER $AI_DB_USER WITH PASSWORD 'strong_password_here';
  REVOKE ALL ON DATABASE $AI_DB_DATABASE FROM PUBLIC;
  GRANT CONNECT ON DATABASE $AI_DB_DATABASE TO $AI_DB_USER;
"
```

### 8.3 SSL/TLS (Production)

```bash
# Generate self-signed cert (development)
openssl req -x509 -nodes -days 365 \
  -newkey rsa:2048 \
  -keyout /etc/ssl/private/ai.key \
  -out /etc/ssl/certs/ai.crt

# Configure Laravel for HTTPS
# In .env: APP_URL=https://yourdomain.com
```

### 8.4 Rate Limiting

Already configured in `app/Http/Middleware/AiChatRateLimiter.php`:
- 20 requests per minute per user
- Adjust if needed

```php
// In config/ai.php
'throttle' => [
    'per_minute' => 20,
    'per_hour' => 500,
],
```

### 8.5 Input Validation

- Prompt injection detector already active
- Audit logs in `storage/logs/prompt-injection-*.log`
- Review periodically for attack patterns

```bash
# Check for injection attempts
grep -c 'INJECTION_DETECTED' storage/logs/prompt-injection-$(date +%Y-%m-%d).log
```

---

## Quick Reference: Daily Admin Checklist

```
[ ] Morning (8:00 AM)
  - [ ] Verify all services running: curl /health endpoints
  - [ ] Check error logs for overnight issues
  - [ ] Verify PostgreSQL + MySQL connections active

[ ] Midday (12:00 PM)
  - [ ] Monitor prediction latency (target < 500ms)
  - [ ] Review any drift alerts in dashboard

[ ] Evening (5:00 PM)
  - [ ] Verify Celery scheduled tasks will run (1am feature eng, 3am drift check)
  - [ ] Backup databases (automated script should run at 2am)

[ ] Weekly (Friday)
  - [ ] Review database size and perform cleanup if needed
  - [ ] Check model retraining job succeeded (Sunday morning)
  - [ ] Audit API token usage and rotate old tokens
  - [ ] Update this runbook with any issues encountered
```

---

**For emergencies, contact the Platform/Data AI team on Slack.**  
**This guide is kept in /home/zippy/Documents/polucon/docs/AI_OPERATIONS_GUIDE.md**

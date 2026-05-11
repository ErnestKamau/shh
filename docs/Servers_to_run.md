# Servers Required to Run Imara AI (Industrialized)

This document lists all processes that must be running for the Imara AI Analytical service to function correctly.

---

## 1. Laravel Application Server

Serves the main web application and the analytics UI.

```bash
bash scripts/run_api.sh
```

> **Endpoint:** `http://127.0.0.1:8000`

---

## 2. Industrialized Unified AI Service (Port 8081)

Provides all AI capabilities through a **deterministic, unified operational mode**. It simplifies query handling by automatically selecting the best approach (SQL Analytics, RAG Knowledge, or Chat).

```bash
# Development (reload enabled)
bash scripts/run_inference.sh

# Production-style local run
AI_SERVICE_RELOAD=false AI_SERVICE_WORKERS=4 bash scripts/run_inference.sh
```

> **Env variable:** `AI_SERVICE_URL=http://127.0.0.1:8081`

---

## 3. Ollama Server (Port 11434)

Provides the local LLM inference engine (Qwen-2-7B or similar) for deep reasoning and conversational fallback.

```bash
# Start the server
ollama serve
```

---

## 4. Analytical PostgreSQL (gcla database)

The primary host for the `reporting` schema and `pgvector` embeddings.

- **Host:** `127.0.0.1` (or your Postgres host)
- **Port:** `5432` (Standard production port)
- **Database:** `gcla`
- **Schemas required:**
    - `reporting`: Contains 30+ tables for sample, ticket, inventory, and equipment analytics.
    - `ai`: Contains vector storage for RAG.
    - `public`: General indices.

---

## 5. Python Background Worker & Scheduler

The "engine" of the system, handling asynchronous tasks and periodic synchronization.

### The Worker (Celery)
Processes RAG indexing, document ingestion, and Sync Pipeline jobs.

```bash
bash scripts/run_worker.sh
```

### The Scheduler (Celery Beat) — CRITICAL
Executes the **5-minute analytical sync** schedule for automatic data parity.

```bash
bash scripts/run_beat.sh
```

---

## 6. Verification & Health Checks

Once all servers are running, verify the analytical health:

```bash
# Validate all 34 AI manifest queries
python3 python/ai_service/scratch/verify_all_manifest_queries.py
```

---

## Quick Start (Terminal Tabs)

| Tab | Component | Command |
|---|---|---|
| 1 | Laravel | `bash scripts/run_api.sh` |
| 2 | AI Service | `bash scripts/run_inference.sh` |
| 3 | Ollama | `ollama serve` |
| 4 | Worker | `bash scripts/run_worker.sh` |
| 5 | Scheduler | `bash scripts/run_beat.sh` |

---

## Environment Variables Checklist

Ensure these are in your `.env`:

| Variable | Recommended Value |
|---|---|
| `AI_SERVICE_URL` | `http://127.0.0.1:8081` |
| `AI_SYNC_CHUNK_SIZE` | Defined in `table_config.yaml` (default 500) |
| `OLLAMA_HOST` | `http://localhost:11434` |
| `CELERY_QUEUES` | `rag,ingestion,sync` |
| `AI_DB_DATABASE` | `gcla` (Centralized PostgreSQL) |
| `DB_DATABASE` | `gcla` (Unified Source) |

#!/usr/bin/env bash
# =============================================================================
# fresh_migrate.sh
# Development utility: fresh migration + full seeding + real users restore.
#
# Usage:
#   ./scripts/fresh_migrate.sh
#   ./scripts/fresh_migrate.sh --no-seed        # migrate only, then restore users
#
# What it does:
#   1. php artisan migrate:fresh --seed   (all 9 phased seeders)
#   2. Restores the real users table from gcla_dump_2026_05_12.dump
#      (clears seeder-generated stub users first)
# =============================================================================
set -euo pipefail

# ── Resolve project root ──────────────────────────────────────────────────────
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

# ── Colour helpers ────────────────────────────────────────────────────────────
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Colour

info()    { echo -e "${GREEN}[fresh_migrate]${NC} $1"; }
warn()    { echo -e "${YELLOW}[fresh_migrate]${NC} $1"; }
error()   { echo -e "${RED}[fresh_migrate]${NC} $1"; exit 1; }

# ── Configuration — reads from project .env ───────────────────────────────────
source_env() {
    local ENV_FILE="$ROOT_DIR/.env"
    if [[ ! -f "$ENV_FILE" ]]; then
        error ".env file not found at $ROOT_DIR/.env"
    fi
    DB_HOST=$(grep -E "^DB_HOST=" "$ENV_FILE" | cut -d= -f2 | tr -d '"' | tr -d "'")
    DB_PORT=$(grep -E "^DB_PORT=" "$ENV_FILE" | cut -d= -f2 | tr -d '"' | tr -d "'")
    DB_DATABASE=$(grep -E "^DB_DATABASE=" "$ENV_FILE" | cut -d= -f2 | tr -d '"' | tr -d "'")
    DB_USERNAME=$(grep -E "^DB_USERNAME=" "$ENV_FILE" | cut -d= -f2 | tr -d '"' | tr -d "'")
    DB_PASSWORD=$(grep -E "^DB_PASSWORD=" "$ENV_FILE" | cut -d= -f2 | tr -d '"' | tr -d "'")

    DB_HOST="${DB_HOST:-127.0.0.1}"
    DB_PORT="${DB_PORT:-5432}"
    DB_DATABASE="${DB_DATABASE:-gcla}"
    DB_USERNAME="${DB_USERNAME:-root}"
    DB_PASSWORD="${DB_PASSWORD:-}"
}

# ── Locate the users dump ─────────────────────────────────────────────────────
find_users_dump() {
    # Look for the canonical dump file
    DUMP_FILE="$ROOT_DIR/gcla_dump_2026_05_12.dump"
    if [[ ! -f "$DUMP_FILE" ]]; then
        # Try any *.dump in the project root as fallback
        DUMP_FILE=$(find "$ROOT_DIR" -maxdepth 1 -name "*.dump" | head -1)
    fi
    if [[ -z "$DUMP_FILE" || ! -f "$DUMP_FILE" ]]; then
        warn "No .dump file found — skipping users restore."
        DUMP_FILE=""
    else
        info "Using dump: $DUMP_FILE"
    fi
}

# ── Parse args ────────────────────────────────────────────────────────────────
SKIP_SEED=false
for arg in "$@"; do
    case $arg in
        --no-seed) SKIP_SEED=true ;;
        --help|-h)
            echo "Usage: ./scripts/fresh_migrate.sh [--no-seed]"
            echo "  --no-seed   Skip phased seeders (migrate only)"
            exit 0
            ;;
    esac
done

# ── Main ──────────────────────────────────────────────────────────────────────
echo ""
echo "========================================================"
echo "  IMARA LIMS — Fresh Migrate + Seed + Users Restore"
echo "========================================================"
echo ""

source_env
find_users_dump

# ── Step 1: Fresh migration + seeding ────────────────────────────────────────
if [[ "$SKIP_SEED" == "true" ]]; then
    info "Step 1: Running fresh migration only (--no-seed)..."
    php artisan migrate:fresh
else
    info "Step 1: Running fresh migration + all phased seeders..."
    php artisan migrate:fresh --seed
fi

echo ""
info "✓ Migration and seeding complete."

# ── Step 2: Restore users from dump ──────────────────────────────────────────
if [[ -z "$DUMP_FILE" ]]; then
    warn "Skipping users restore — no dump file found."
    echo ""
    echo "========================================================"
    echo "  DONE (without users restore)"
    echo "========================================================"
    exit 0
fi

echo ""
info "Step 2: Restoring real users from dump..."

# Remove any stub users created by seeders (Phase 1, 6) before restoring
# from the authoritative dump. Use session_replication_role to bypass FKs.
info "  Clearing seeder-generated users..."
PGPASSWORD="$DB_PASSWORD" psql \
    -h "$DB_HOST" -p "$DB_PORT" \
    -U "$DB_USERNAME" -d "$DB_DATABASE" \
    -c "SET session_replication_role = 'replica'; DELETE FROM public.users;" \
    -q

# Restore only the users table data from the dump
info "  Importing users table from dump (data only, triggers disabled)..."
PGPASSWORD="$DB_PASSWORD" pg_restore \
    -h "$DB_HOST" -p "$DB_PORT" \
    -U "$DB_USERNAME" -d "$DB_DATABASE" \
    -t users \
    --data-only \
    --disable-triggers \
    "$DUMP_FILE" 2>/dev/null || true
    # pg_restore exits non-zero when tables exist — suppress noise.

# Verify
USER_COUNT=$(PGPASSWORD="$DB_PASSWORD" psql \
    -h "$DB_HOST" -p "$DB_PORT" \
    -U "$DB_USERNAME" -d "$DB_DATABASE" \
    -tAc "SELECT COUNT(*) FROM public.users;")

info "  ✓ Restored ${USER_COUNT} user(s) from dump."

# ── Step 3: Post-restore — update AI request logs table to accept UUID company_id ──
# The users restored from the dump have UUID company_ids. The ai.ai_request_logs
# column is already VARCHAR(128) so no migration needed.

# ── Step 4: Flush Laravel caches ──────────────────────────────────────────────
echo ""
info "Step 3: Flushing Laravel caches..."
php artisan cache:clear  -q
php artisan config:clear -q

echo ""
echo "========================================================"
echo "  ✓ DONE — Database is ready."
echo "  Users restored: ${USER_COUNT}"
echo "  Dump used:      $(basename "$DUMP_FILE")"
echo "========================================================"
echo ""

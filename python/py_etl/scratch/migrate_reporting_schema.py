import sys
import os
sys.path.append(os.getcwd())

from py_etl.core.database import db_manager

ddl = """
-- Analytical Expansion Schema initialization (V2)

CREATE TABLE IF NOT EXISTS reporting.users (
    source_id BIGINT PRIMARY KEY,
    name TEXT,
    email TEXT,
    department_id BIGINT,
    lab_section_id BIGINT,
    position TEXT,
    designation TEXT,
    active BOOLEAN DEFAULT TRUE,
    source_created_at TIMESTAMPTZ,
    synced_at TIMESTAMPTZ,
    payload JSONB
);

-- Ensure designation exists if table was already created
ALTER TABLE reporting.users ADD COLUMN IF NOT EXISTS designation TEXT;

CREATE TABLE IF NOT EXISTS reporting.clients (
    source_id BIGINT PRIMARY KEY,
    code TEXT,
    name TEXT,
    email TEXT,
    active BOOLEAN DEFAULT TRUE,
    country_id BIGINT,
    zoho_id TEXT,
    source_created_at TIMESTAMPTZ,
    source_updated_at TIMESTAMPTZ,
    synced_at TIMESTAMPTZ,
    payload JSONB
);

CREATE TABLE IF NOT EXISTS reporting.ticket_priorities (
    source_id BIGINT PRIMARY KEY,
    name TEXT,
    source_created_at TIMESTAMPTZ,
    source_updated_at TIMESTAMPTZ,
    synced_at TIMESTAMPTZ,
    payload JSONB
);

CREATE TABLE IF NOT EXISTS reporting.ticket_statuses (
    source_id BIGINT PRIMARY KEY,
    name TEXT,
    source_created_at TIMESTAMPTZ,
    source_updated_at TIMESTAMPTZ,
    synced_at TIMESTAMPTZ,
    payload JSONB
);

CREATE TABLE IF NOT EXISTS reporting.ticket_categories (
    source_id BIGINT PRIMARY KEY,
    name TEXT,
    source_created_at TIMESTAMPTZ,
    source_updated_at TIMESTAMPTZ,
    synced_at TIMESTAMPTZ,
    payload JSONB
);

CREATE TABLE IF NOT EXISTS reporting.departments (
    source_id BIGINT PRIMARY KEY,
    name TEXT,
    source_created_at TIMESTAMPTZ,
    source_updated_at TIMESTAMPTZ,
    synced_at TIMESTAMPTZ,
    payload JSONB
);

CREATE TABLE IF NOT EXISTS reporting.sample_types (
    source_id BIGINT PRIMARY KEY,
    name TEXT,
    source_created_at TIMESTAMPTZ,
    source_updated_at TIMESTAMPTZ,
    synced_at TIMESTAMPTZ,
    payload JSONB
);
"""

try:
    print("Applying analytical schema migrations (V2) to pgsql_ai...")
    db_manager.execute_postgres_sql(ddl)
    print("✅ Schema migrations completed successfully.")
except Exception as e:
    print(f"❌ Migration failed: {e}")
    sys.exit(1)

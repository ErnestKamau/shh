"""
seed_manifest_db.py — Authoritative Manifest & Intent Patterns Seeder

Populates PostgreSQL tables ai.manifest_intents and ai.manifest_intent_patterns
using current configurations from live_data_manifest.json and manifest_intent_router.py.
"""
from __future__ import annotations

import json
from pathlib import Path
from loguru import logger
from sqlalchemy import text

import sys
sys.path.append('.')
from python.py_pipeline.core.database import db_manager
from python.ai_service.core.manifest_intent_router import _GROUP_A_RULES, _GROUP_B_RULES


def seed_database():
    manifest_path = Path(__file__).resolve().parents[0] / "live_data_manifest.json"
    if not manifest_path.exists():
        logger.error(f"live_data_manifest.json not found at {manifest_path}")
        return

    with manifest_path.open("r", encoding="utf-8") as f:
        manifest_data = json.load(f)

    # 1. Gather all patterns and groups from manifest_intent_router.py
    intent_patterns = {}  # intent_id -> list of patterns
    intent_groups = {}     # intent_id -> 'A' or 'B'

    # Process Group A
    for domain, rules in _GROUP_A_RULES.items():
        for patterns, intent_id in rules:
            intent_patterns.setdefault(intent_id, []).extend(patterns)
            intent_groups[intent_id] = 'A'

    # Process Group B
    for domain, rules in _GROUP_B_RULES.items():
        for patterns, intent_id in rules:
            intent_patterns.setdefault(intent_id, []).extend(patterns)
            intent_groups[intent_id] = 'B'

    logger.info("Connecting to PostgreSQL to seed manifest intents...")
    with db_manager.postgres_connection() as conn:
        # Clear existing data to avoid conflicts on refresh
        conn.execute(text("TRUNCATE TABLE ai.manifest_intent_patterns CASCADE"))
        conn.execute(text("TRUNCATE TABLE ai.manifest_intents CASCADE"))
        logger.info("Cleared existing manifest tables.")

        intents_inserted = 0
        patterns_inserted = 0

        # Loop through domains in the JSON manifest
        for domain, intents in manifest_data.items():
            for intent_id, info in intents.items():
                group_type = intent_groups.get(intent_id, 'A')
                sql_query = info.get("sql", "")
                description = info.get("description", "")
                output_format = info.get("output_format", "table")
                ttl_seconds = info.get("ttl_seconds", 120)

                # Insert intent
                conn.execute(
                    text("""
                        INSERT INTO ai.manifest_intents 
                            (id, domain, group_type, sql_query, description, output_format, ttl_seconds, active, created_at, updated_at)
                        VALUES 
                            (:id, :domain, :group_type, :sql_query, :description, :output_format, :ttl_seconds, true, NOW(), NOW())
                    """),
                    {
                        "id": intent_id,
                        "domain": domain,
                        "group_type": group_type,
                        "sql_query": sql_query,
                        "description": description,
                        "output_format": output_format,
                        "ttl_seconds": ttl_seconds,
                    }
                )
                intents_inserted += 1

                # Insert patterns if any
                patterns = intent_patterns.get(intent_id, [])
                for pattern in patterns:
                    conn.execute(
                        text("""
                            INSERT INTO ai.manifest_intent_patterns 
                                (intent_id, pattern, created_at, updated_at)
                            VALUES 
                                (:intent_id, :pattern, NOW(), NOW())
                        """),
                        {
                            "intent_id": intent_id,
                            "pattern": pattern.lower().strip()
                        }
                    )
                    patterns_inserted += 1

        conn.commit()
        logger.success(
            f"Successfully seeded manifest: {intents_inserted} intents and "
            f"{patterns_inserted} patterns loaded into 'ai' schema."
        )


if __name__ == "__main__":
    seed_database()

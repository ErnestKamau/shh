import json
import time
import logging
from typing import Dict, Any
import sys
import os

# Ensure project root is in sys.path
sys.path.append(os.getcwd())

from python.py_etl.core.database import DatabaseManager

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

def verify_manifest():
    manifest_path = "python/ai_service/core/live_data_manifest.json"
    with open(manifest_path, 'r') as f:
        manifest = json.load(f)

    db = DatabaseManager()
    # We use pgsql_ai as the primary analytical repo
    # We use pgsql_ai as the primary analytical repo
    engine = db.postgres_engine
    
    report = []
    
    print(f"{'DOMAIN':<12} | {'INTENT':<30} | {'RUN 1':<10} | {'RUN 2':<10} | {'STATUS'}")
    print("-" * 80)

    from sqlalchemy import text

    for domain, intents in manifest.items():
        for intent_name, config in intents.items():
            sql = config['sql']
            
            latencies = []
            errors = []
            
            for i in range(2):
                start = time.time()
                try:
                    with engine.connect() as connection:
                        safe_sql = sql
                        # If it's a count, just run it. If it's a table, limit it.
                        is_count = config.get('output_format') == 'count'
                        
                        if config.get('output_format') == 'table' and 'LIMIT' not in sql.upper():
                            safe_sql = f"{sql} LIMIT 1"
                        
                        result = connection.execute(text(safe_sql))
                        rows = result.fetchall()
                        row_count = len(rows)
                        
                        if is_count and rows:
                           row_count = rows[0][0]
                           
                    latencies.append(int((time.time() - start) * 1000))
                    if i == 0:
                        report_val = row_count
                except Exception as e:
                    errors.append(str(e))
                    latencies.append(-1)
                    report_val = "N/A"

            status = "✅ PASS" if not errors else "❌ FAIL"
            run1 = f"{latencies[0]}ms" if latencies[0] >= 0 else "N/A"
            run2 = f"{latencies[1]}ms" if latencies[1] >= 0 else "N/A"
            
            print(f"{domain:<12} | {intent_name:<30} | {run1:<10} | {run2:<10} | {status:<8} | Count/Val: {report_val}")
            
            if errors:
                print(f"   [!] Error: {errors[0][:120]}...")

    print("-" * 80)

if __name__ == "__main__":
    verify_manifest()

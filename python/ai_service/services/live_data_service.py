import os
import redis
import logging
import time
import hashlib
import json
from typing import Dict, Any, List, Optional
from datetime import datetime
import pandas as pd
from sqlalchemy import text
from python.py_etl.core.database import db_manager

logger = logging.getLogger(__name__)

class LiveDataService:
    """
    Executes deterministic SQL queries based on a manifest of approved templates.
    Enforces strict read-only parameters, TTL caching, timeouts, and circuit breaking.
    """
    
    def __init__(self):
        self.manifest_path = os.path.join(
            os.path.dirname(__file__), 
            "..", "core", "live_data_manifest.json"
        )
        self.templates = self._load_manifest()
        
        # Caching
        self._cache: Dict[str, Dict[str, Any]] = {}
        
        # Circuit Breaker
        self._consecutive_failures = 0
        self._circuit_open = False
        self._circuit_reset_time = 0
        self.MAX_FAILURES = 5
        self.CIRCUIT_COOLDOWN_SECONDS = 60
        
        # Redis Heartbeat
        redis_url = os.getenv("CELERY_BROKER_URL", "redis://localhost:6379/0")
        self._redis = redis.from_url(redis_url)

    def _load_manifest(self) -> Dict[str, Any]:
        try:
            with open(self.manifest_path, 'r') as f:
                return json.load(f)
        except Exception as e:
            logger.error(f"Failed to load LiveData manifest: {e}")
            return {}

    def _generate_cache_key(self, intent: str, params: Optional[Dict[str, Any]]) -> str:
        param_str = json.dumps(params, sort_keys=True) if params else "{}"
        hash_digest = hashlib.md5(param_str.encode()).hexdigest()
        return f"{intent}:{hash_digest}"

    def execute_step(self, intent: str, params: Optional[Dict[str, Any]] = None) -> Dict[str, Any]:
        """
        Main entry point for executing an aggregate fact step safely.
        """
        # 0. Check ETL Sync Status First
        failed_tables = self._check_reporting_freshness()
        etl_caveat = ""
        if failed_tables:
            etl_caveat = (
                "\n\n(Note: Some data sources appear delayed. "
                "These live counts are based on the latest available snapshot, not real-time.)"
            )

        # 1. Circuit Breaker Check
        if self._circuit_open:
            if time.time() > self._circuit_reset_time:
                logger.info("Live Data Circuit Breaker: Cooling down, attempting partial reset.")
                self._circuit_open = False
                self._consecutive_failures = 0
            else:
                logger.warning(f"Live Data Circuit Breaker is OPEN. Rejecting query '{intent}'.")
                return {
                    "intent": intent,
                    "summary": "Live data temporarily unavailable (Circuit Broken). Please prioritize semantic reasoning.",
                    "success": False
                }

        # 2. Resolve template
        template = self._resolve_template(intent)
        if not template:
            return {"error": f"Intent '{intent}' not found in manifest", "success": False}

        # 3. Caching Strategy
        cache_key = self._generate_cache_key(intent, params)
        ttl = template.get("ttl_seconds", 10)
        
        cached_entry = self._cache.get(cache_key)
        if cached_entry and time.time() < cached_entry["expires_at"]:
            logger.info(f"LiveData cache HIT for {cache_key}")
            cached_response = cached_entry["response"]
            # Inject caveat if ETL failed since cache was written
            if etl_caveat and etl_caveat not in cached_response["summary"]:
                cached_response["summary"] += etl_caveat
            return cached_response

        logger.info(f"LiveData cache MISS for {cache_key}. Executing against PostgreSQL.")
        
        # 4. Execute SQL (with bounded execution)
        start_time = time.time()
        try:
            sql = template["sql"]
            # Enforce Limit at application layer if the template forgot it to prevent OOM
            if "LIMIT" not in sql.upper():
                 sql += " LIMIT 50"

            with db_manager.postgres_connection() as conn:
                # Enforce server-side query timeout for postgres
                try:
                    conn.execute(text("SET LOCAL statement_timeout = '5000ms'"))
                except Exception:
                    pass
                
                df = pd.read_sql(text(sql), conn, params=params or {})

            # 5. Success Reset
            self._consecutive_failures = 0
            
            # 6. Format result
            formatted = self._format_result(df, template["output_format"], template["description"], intent)
            formatted += etl_caveat
            scalar_value = self._extract_scalar_value(df, template["output_format"])
            
            response = {
                "intent": intent,
                "data": df.fillna("").to_dict(orient="records"),
                "summary": formatted,
                "success": True,
                "value": scalar_value,
                "sql": sql,
                "execution_latency_ms": round((time.time() - start_time) * 1000)
            }
            
            # Save to Cache
            self._cache[cache_key] = {
                "response": response,
                "expires_at": time.time() + ttl
            }
            
            # Simple eviction if cache gets too large 
            if len(self._cache) > 500:
                self._cache.pop(next(iter(self._cache)))
                
            return response

        except Exception as e:
            logger.error(f"LiveData execution failed for {intent}. Reason: {e}")
            self._consecutive_failures += 1
            if self._consecutive_failures >= self.MAX_FAILURES:
                self._circuit_open = True
                self._circuit_reset_time = time.time() + self.CIRCUIT_COOLDOWN_SECONDS
                logger.critical(f"Live Data Circuit Breaker TRIPPED due to {self.MAX_FAILURES} consecutive errors.")
                
            return {
                "error": str(e), 
                "success": False,
                "summary": "The reporting database is currently under heavy load or unavailable. Proceed with qualitative evidence, but state explicitly that exact real-time counts could not be retrieved."
            }

    def _check_reporting_freshness(self) -> list[str]:
        """Check the status of reporting tables via background heartbeat."""
        try:
            raw_heartbeat = self._redis.get("imara:ai:etl_heartbeat")
            if raw_heartbeat:
                data = json.loads(raw_heartbeat)
                # If the heartbeat is fresh (within 5 mins), return the stored failures
                updated_at = datetime.fromisoformat(data.get("updated_at"))
                if (datetime.now() - updated_at).total_seconds() < 300:
                    return data.get("failed_tables", [])
            
            # Fallback/Bootstrap: Perform one-time sync check if Redis is empty or stale
            from python.py_etl.services.etl_index_state_service import etl_index_state_service
            tables_to_check = ["sample_headers", "sample_details"]
            failed = []
            for t in tables_to_check:
                state = etl_index_state_service.get_state(t)
                if not state or state.get("etl_status") != "success":
                    failed.append(t)
            return failed
        except Exception as e:
            logger.warning(f"Heartbeat lookup failed, falling back to permissive mode: {e}")
            return []

    def _resolve_template(self, intent: str) -> Optional[Dict[str, Any]]:
        for domain_name, domain_templates in self.templates.items():
            if intent in domain_templates:
                return domain_templates[intent]
        return None

    def _format_result(self, df: pd.DataFrame, output_format: str, description: str, intent: str = "") -> str:
        source_line = f"\n\n_Source: live operational database · Route: `{intent}`_" if intent else ""

        if df.empty:
            return (
                f"No matching records were found for '{description}'.\n"
                "This may indicate the data hasn't been synced yet, "
                "or no records match the current filters."
                f"{source_line}"
            )

        if output_format == "count":
            val = df.iloc[0, 0]
            # Format large numbers with commas
            try:
                val_display = f"{int(val):,}" if float(val) == int(float(val)) else str(val)
            except (ValueError, TypeError):
                val_display = str(val)
            return f"There are **{val_display}** {description}.{source_line}"

        if output_format == "percentage":
            val = df.iloc[0, 0]
            return f"The {description} is **{val}%**.{source_line}"

        if output_format == "table":
            markdown = f"**{description.capitalize()}:**\n\n"
            markdown += df.head(10).to_markdown(index=False)
            if len(df) > 10:
                markdown += f"\n\n*(Showing top 10 of {len(df)} records)*"
            markdown += source_line
            return markdown

        return df.to_json(orient="records") + source_line

    def _extract_scalar_value(self, df: pd.DataFrame, output_format: str) -> Optional[Any]:
        """Return the primary scalar for count/percentage outputs."""
        if df.empty:
            return None
        if output_format not in {"count", "percentage"}:
            return None
        return df.iloc[0, 0]

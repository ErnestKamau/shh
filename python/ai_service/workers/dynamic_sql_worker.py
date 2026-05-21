"""
workers/dynamic_sql_worker.py — Dynamic (Free-hand) SQL Worker

Generates and safely executes PostgreSQL SELECT queries from natural language.
Used when the ManifestWorker finds no matching pre-approved intent.

Safety guarantees:
  1. Uses the schema_catalog to restrict visible tables to the active mode's domains.
  2. Generates SQL via LLM with explicit rules: SELECT-only, reporting schema, LIMIT 100.
  3. Validates the generated SQL with SQLGuardrails before executing.
  4. Runs EXPLAIN first to catch syntax errors; retries with the error message once.
  5. Executes through db_manager using the read-only connection.
  6. 5-second server-side statement_timeout enforced via SET LOCAL.
"""

import logging
import re
import json
import time
import concurrent.futures
from typing import Optional, Dict, Any, List

from sqlalchemy import text
from python.py_pipeline.core.database import db_manager
from python.ai_service.services.ollama_service import OllamaService
from python.ai_service.services.visualization_service import VisualizationService
from python.ai_service.core.sql_guardrails import SQLGuardrails
from python.ai_service.core.schema_catalog import build_prompt_schema, BLOCKED_TABLES, BLOCKED_SCHEMAS
from python.ai_service.core import mode_registry

logger = logging.getLogger(__name__)

_LLM_TIMEOUT = 8.0
_SQL_EXEC_TIMEOUT = 5.0
_MAX_ROWS = 100


class DynamicSqlWorker:
    """
    Generates and executes free-hand SQL from natural language using the
    schema catalog scoped to the active mode's allowed domains.
    """

    def __init__(self, ollama: OllamaService, visualizer: VisualizationService):
        self.ollama = ollama
        self.visualizer = visualizer
        self.guardrails = SQLGuardrails()

    def run(
        self,
        message: str,
        company_id: int = 1,
        use_visuals: bool = True,
        mode: Optional[str] = None,
        trace_id: str = "",
    ) -> Optional[Dict[str, Any]]:
        """
        Attempt to generate and execute a dynamic SQL query.
        Returns a result dict on success/controlled failure.
        Returns None if the query clearly cannot be answered with SQL.
        """
        logger.info(f"DynamicSqlWorker [{trace_id[:8]}]: Generating SQL for: {message[:60]}")
        start = time.time()

        # ── Step 1: Build schema context ─────────────────────────────────────
        schema_prompt = build_prompt_schema(mode)

        # ── Step 2: Generate SQL with LLM ────────────────────────────────────
        sql = self._generate_sql(message, schema_prompt, company_id, mode, trace_id)
        if not sql:
            logger.warning(f"DynamicSqlWorker [{trace_id[:8]}]: LLM returned no SQL")
            return None

        # ── Step 3: Guardrail validation ──────────────────────────────────────
        if not self.guardrails.validate_query(sql):
            logger.warning(f"DynamicSqlWorker [{trace_id[:8]}]: SQL failed guardrail — blocked")
            return {
                "answer": "I generated a query for that, but it didn't pass the safety checks. "
                          "Please rephrase your question or ask for a specific report.",
                "sources": [],
                "meta": {
                    "route": "dynamic_sql:blocked",
                    "routing_tier": "dynamic_sql",
                    "confidence": 0.0,
                },
            }

        # Additional blocked-table check beyond SQLGuardrails
        if self._contains_blocked_table(sql):
            logger.warning(f"DynamicSqlWorker [{trace_id[:8]}]: SQL references blocked table — rejected")
            return {
                "answer": "That query references restricted data that is not available in this mode.",
                "sources": [],
                "meta": {"route": "dynamic_sql:blocked_table", "routing_tier": "dynamic_sql"},
            }

        # ── Step 4: EXPLAIN check + optional retry ────────────────────────────
        sql, explain_error = self._validate_with_explain(sql, trace_id)
        if explain_error and not sql:
            return {
                "answer": "I tried to answer that analytically, but couldn't construct a valid query. "
                          "Try rephrasing, or ask for one of the standard reports.",
                "sources": [],
                "meta": {"route": "dynamic_sql:explain_failed", "routing_tier": "dynamic_sql"},
            }

        # ── Step 5: Execute ───────────────────────────────────────────────────
        result = self._execute(sql, company_id, trace_id)

        if result is None:
            return {
                "answer": "The dynamic query timed out or failed. Try a more specific question.",
                "sources": [],
                "meta": {"route": "dynamic_sql:exec_failed", "routing_tier": "dynamic_sql"},
            }

        data, rows = result

        # ── Step 6: Synthesize answer ──────────────────────────────────────────
        answer = self._synthesize(message, data, rows, sql, mode, use_visuals)

        latency = round((time.time() - start) * 1000)
        logger.info(f"DynamicSqlWorker [{trace_id[:8]}]: OK — {rows} rows in {latency}ms")

        return {
            "answer": answer,
            "sources": [],
            "meta": {
                "route": "dynamic_sql",
                "routing_tier": "dynamic_sql",
                "confidence": 0.85,
                "row_count": rows,
                "latency_ms": latency,
                "generated_sql": sql,  # For traceability/debugging
            },
        }

    # ── SQL Generation ────────────────────────────────────────────────────────

    def _generate_sql(
        self,
        message: str,
        schema_prompt: str,
        company_id: int,
        mode: Optional[str],
        trace_id: str,
    ) -> Optional[str]:
        persona = mode_registry.get_persona(mode)
        allowed_domains = mode_registry.get_allowed_domains(mode)
        domain_note = "" if "*" in allowed_domains else (
            f"IMPORTANT: Only query tables in these domains: {', '.join(allowed_domains)}."
        )

        prompt = f"""You are a PostgreSQL expert generating a safe, read-only analytics query for a LIMS.
Context: {persona}
{domain_note}

{schema_prompt}

User Question: "{message}"

STRICT RULES:
1. Output ONLY a single raw SQL SELECT statement — no explanation, no markdown, no comments.
2. ONLY use tables listed above in the 'public' schema. Prefix every table with 'public.'.
3. Always include LIMIT {_MAX_ROWS} at the end.
4. Do NOT use INSERT, UPDATE, DELETE, DROP, TRUNCATE, CREATE, GRANT, or any write operation.
5. Do NOT access columns marked as sensitive (e.g. passwords, tokens).
6. Use parameterised syntax only for company_id: WHERE ... company_id = :company_id
7. All timestamps should be cast as ::timestamptz for consistent comparison.
8. If the question cannot be answered with SQL, output exactly: CANNOT_ANSWER

SQL:"""

        try:
            with concurrent.futures.ThreadPoolExecutor(max_workers=1) as ex:
                raw = ex.submit(self.ollama.generate, prompt=prompt).result(timeout=_LLM_TIMEOUT)
        except Exception as exc:
            logger.error(f"DynamicSqlWorker [{trace_id[:8]}]: LLM generation failed: {exc}")
            return None

        raw = raw.strip()
        if "CANNOT_ANSWER" in raw.upper():
            return None

        # Strip markdown fences if the model ignores rules
        sql = re.sub(r"^```(?:sql)?", "", raw, flags=re.IGNORECASE).strip()
        sql = re.sub(r"```$", "", sql).strip()

        # Enforce LIMIT
        if "LIMIT" not in sql.upper():
            sql += f" LIMIT {_MAX_ROWS}"

        return sql

    # ── EXPLAIN Validation ────────────────────────────────────────────────────

    def _validate_with_explain(self, sql: str, trace_id: str):
        """Run EXPLAIN to catch syntax errors. Returns (sql, error_msg)."""
        try:
            with db_manager.postgres_connection() as conn:
                conn.execute(text(f"EXPLAIN {sql}"))
            return sql, None
        except Exception as exc:
            error_msg = str(exc)
            logger.warning(f"DynamicSqlWorker [{trace_id[:8]}]: EXPLAIN failed: {error_msg[:100]}")

            # One retry: feed the error back to the LLM
            try:
                retry_prompt = f"""The following PostgreSQL query has a syntax error.
Fix it and output ONLY the corrected SQL (no explanation, no markdown).

Original SQL:
{sql}

Error:
{error_msg}

Corrected SQL:"""
                with concurrent.futures.ThreadPoolExecutor(max_workers=1) as ex:
                    raw = ex.submit(self.ollama.generate, prompt=retry_prompt).result(timeout=_LLM_TIMEOUT)
                fixed = raw.strip().lstrip("```sql").lstrip("```").rstrip("```").strip()
                if "LIMIT" not in fixed.upper():
                    fixed += f" LIMIT {_MAX_ROWS}"
                if self.guardrails.validate_query(fixed):
                    # Test the fix with EXPLAIN once more
                    with db_manager.postgres_connection() as conn:
                        conn.execute(text(f"EXPLAIN {fixed}"))
                    return fixed, None
            except Exception as retry_exc:
                logger.error(f"DynamicSqlWorker [{trace_id[:8]}]: Retry failed: {retry_exc}")

            return None, error_msg

    # ── Query Execution ───────────────────────────────────────────────────────

    def _execute(self, sql: str, company_id: int, trace_id: str):
        """Execute the SQL. Returns (data: List[Dict], row_count: int) or None."""
        try:
            with concurrent.futures.ThreadPoolExecutor(max_workers=1) as ex:
                future = ex.submit(self._run_query, sql, company_id)
                return future.result(timeout=_SQL_EXEC_TIMEOUT)
        except concurrent.futures.TimeoutError:
            logger.warning(f"DynamicSqlWorker [{trace_id[:8]}]: Execution timed out")
            return None
        except Exception as exc:
            logger.error(f"DynamicSqlWorker [{trace_id[:8]}]: Execution failed: {exc}")
            return None

    def _run_query(self, sql: str, company_id: int):
        with db_manager.postgres_connection() as conn:
            try:
                conn.execute(text("SET LOCAL statement_timeout = '5000ms'"))
            except Exception:
                pass
            rows = conn.execute(text(sql), {"company_id": company_id}).mappings().all()
            data = [dict(row) for row in rows]
        return data, len(data)

    # ── Result Synthesis ──────────────────────────────────────────────────────

    def _synthesize(
        self,
        message: str,
        data: List[Dict],
        row_count: int,
        sql: str,
        mode: Optional[str],
        use_visuals: bool,
    ) -> str:
        if not data:
            return (
                "The query ran successfully but returned no matching records. "
                "The data may not exist for the filters applied."
            )

        # Format as markdown table
        if data:
            headers = list(data[0].keys())
            header_row = " | ".join(headers)
            sep_row = " | ".join(["---"] * len(headers))
            rows = "\n".join(
                " | ".join(str(row.get(h, "")) for h in headers)
                for row in data[:20]
            )
            table_md = f"| {header_row} |\n| {sep_row} |\n" + "\n".join(
                f"| {' | '.join(str(row.get(h, '')) for h in headers)} |"
                for row in data[:20]
            )
            if row_count > 20:
                table_md += f"\n\n*(Showing top 20 of {row_count} records)*"
        else:
            table_md = ""

        persona = mode_registry.get_persona(mode)
        explanation_prompt = f"""{persona}

A database query was run to answer the user's question. Summarise the results briefly and professionally.
Do not repeat all the table data; provide a 2-3 sentence insight.

User Question: "{message}"

Query Results (up to 20 rows shown):
{table_md}

Summary:"""

        try:
            with concurrent.futures.ThreadPoolExecutor(max_workers=1) as ex:
                explanation = ex.submit(self.ollama.generate, prompt=explanation_prompt).result(timeout=6.0)
        except Exception:
            explanation = f"Query returned {row_count} records."

        source_note = f"\n\n_Source: dynamic query · {row_count} records · `public` schema_"
        return f"{table_md}\n\n{explanation}{source_note}"

    # ── Utilities ─────────────────────────────────────────────────────────────

    def _contains_blocked_table(self, sql: str) -> bool:
        sql_lower = sql.lower()
        for table in BLOCKED_TABLES:
            if re.search(rf"\b{re.escape(table)}\b", sql_lower):
                return True
        for schema in BLOCKED_SCHEMAS:
            if schema in sql_lower:
                return True
        return False

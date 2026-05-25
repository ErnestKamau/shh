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

_LLM_TIMEOUT = 45.0
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
        schema_prompt = build_prompt_schema(mode, query=message)

        # ── Step 2: Generate SQL with LLM ────────────────────────────────────
        message_lower = message.lower()
        sql = None
        if self._is_analyst_performance_query(message_lower):
            sql = self._analyst_performance_sql(message_lower)
        if not sql:
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
        sql, explain_error = self._validate_with_explain(sql, trace_id, message)
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
        query_hints = self._query_hints(message)

        prompt = f"""You are a PostgreSQL expert generating a safe, read-only analytics query for a LIMS.
Context: {persona}
{domain_note}

{schema_prompt}

Query-specific guidance:
{query_hints}

User Question: "{message}"

STRICT RULES:
1. Output ONLY a single raw SQL SELECT statement — no explanation, no markdown, no comments.
2. ONLY use tables listed above in the 'public' schema. Prefix every table with 'public.'.
3. ONLY use the exact column names shown in the schema above. NEVER invent or assume columns not listed.
4. Always include LIMIT {_MAX_ROWS} at the end.
5. Do NOT use INSERT, UPDATE, DELETE, DROP, TRUNCATE, CREATE, GRANT, or any write operation.
6. Do NOT access columns marked as sensitive (e.g. passwords, tokens).
7. Use parameterised syntax only for company_id: WHERE ... company_id = :company_id
8. All timestamps should be cast as ::timestamptz for consistent comparison.
9. For sample workflow/status questions, use public.sample_headers.status and public.sample_headers.isactive.
10. NEVER filter public.sample_details by isactive, status, sample_tracking_stage, or created_at.
11. If using public.sample_details with workflow/status filters, JOIN public.sample_headers first.
12. If the question cannot be answered with SQL, output exactly: CANNOT_ANSWER

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

        sql = self._extract_select(raw)
        if not sql:
            logger.warning(f"DynamicSqlWorker [{trace_id[:8]}]: No SELECT found in LLM output: {raw[:80]}")
            return None

        # Enforce LIMIT
        if "LIMIT" not in sql.upper():
            sql += f" LIMIT {_MAX_ROWS}"

        return sql

    # ── EXPLAIN Validation ────────────────────────────────────────────────────

    def _validate_with_explain(self, sql: str, trace_id: str, message: str = ""):
        """Run EXPLAIN to catch syntax errors. Returns (sql, error_msg)."""
        try:
            with db_manager.postgres_connection() as conn:
                conn.execute(text(f"EXPLAIN {sql}"))
            return sql, None
        except Exception as exc:
            error_msg = str(exc)
            logger.warning(f"DynamicSqlWorker [{trace_id[:8]}]: EXPLAIN failed: {error_msg[:100]}")

            deterministic_fix = self._deterministic_sql_fix(sql, error_msg, message)
            if deterministic_fix and self.guardrails.validate_query(deterministic_fix):
                try:
                    with db_manager.postgres_connection() as conn:
                        conn.execute(text(f"EXPLAIN {deterministic_fix}"))
                    logger.info(f"DynamicSqlWorker [{trace_id[:8]}]: EXPLAIN recovered by deterministic SQL fix")
                    return deterministic_fix, None
                except Exception as deterministic_exc:
                    logger.warning(
                        f"DynamicSqlWorker [{trace_id[:8]}]: Deterministic SQL fix failed: {deterministic_exc}"
                    )

            # One retry: feed the error back to the LLM
            try:
                retry_hints = self._query_hints(message) if message else self._sample_workflow_rules()
                retry_prompt = f"""The following PostgreSQL query has a syntax error.
Fix it and output ONLY the corrected SQL (no explanation, no markdown).

Schema correction rules:
{retry_hints}
- Only use columns that exist on the table where they are referenced.
- If PostgreSQL reports "missing FROM-clause entry", add the required table with an explicit JOIN.
- If PostgreSQL reports a column does not exist on sample_details, move workflow/status/active filters to public.sample_headers.

Original SQL:
{sql}

Error:
{error_msg}

Corrected SQL:"""
                with concurrent.futures.ThreadPoolExecutor(max_workers=1) as ex:
                    raw = ex.submit(self.ollama.generate, prompt=retry_prompt).result(timeout=_LLM_TIMEOUT)
                fixed = self._extract_select(raw)
                if not fixed:
                    return None, error_msg
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

    def _extract_select(self, raw: str) -> Optional[str]:
        """Extract one clean SELECT statement from a noisy model response."""
        sql = raw.strip()
        sql = re.sub(r"^```(?:sql)?", "", sql, flags=re.IGNORECASE).strip()
        sql = re.sub(r"```$", "", sql).strip()

        select_match = re.search(r"(SELECT\b.*)", sql, re.IGNORECASE | re.DOTALL)
        if not select_match:
            return None

        sql = select_match.group(1).strip()
        lines = sql.splitlines()
        clean_lines = []
        for line in lines:
            stripped = line.strip()
            if not stripped:
                break
            if clean_lines and re.match(r"^(This|Note|The|Here|Please|I |Result)", stripped):
                break
            clean_lines.append(line)

        sql = "\n".join(clean_lines).strip()
        sql = re.sub(r";.*$", "", sql, flags=re.DOTALL).strip()
        return sql or None

    def _query_hints(self, message: str) -> str:
        """Return deterministic hints for common LIMS wording without replacing Dynamic SQL."""
        m = message.lower()
        hints = [self._sample_workflow_rules()]

        workflow_map = [
            (("verification", "verified", "verify"), "Sample Verification"),
            (("approval", "approved", "approve"), "Sample Approval"),
            (("in lab", "laboratory", "analysis in progress"), "Samples In Lab"),
            (("received", "receipt"), "Sample Received"),
            (("completed", "complete"), "Completed"),
            (("rejected", "cancelled", "canceled"), "Rejected"),
        ]

        for keywords, status in workflow_map:
            if any(keyword in m for keyword in keywords):
                hints.append(
                    f"- This question appears to target workflow status '{status}'. "
                    f"Use public.sample_headers.status = '{status}'."
                )
                break

        if any(word in m for word in ("sample", "samples", "batch", "batches")):
            hints.append(
                "- For counts of sample batches, count public.sample_headers rows. "
                "For individual sample items only, count public.sample_details rows joined to public.sample_headers."
            )

        if any(phrase in m for phrase in ("sample type", "sample types", "specimen type", "matrix type")):
            hints.append(
                "- For sample volume/distribution by sample type, join public.sample_headers to public.sample_types "
                "and select public.sample_types.name. Do not display sample_type_id to users."
            )

        if any(word in m for word in ("analyst", "analysts", "staff", "personnel", "team", "performance", "rank")):
            hints.append(
                "- For generic analyst/staff/personnel performance or ranking, combine verified batches, approved batches, "
                "and result-entry activity from track_sample_results, track_control_results, and track_media_results. "
                "For explicit verification or approval performance, use that specific public.sample_headers user column. "
                "Never use public.analytes for analyst performance."
            )

        if any(word in m for word in ("bar", "chart", "graph", "breakdown", "by stage", "by status", "workflow stage")):
            hints.append(
                "- For workflow bar charts/breakdowns, SELECT public.sample_headers.status and COUNT(*) grouped by status."
            )

        return "\n".join(hints)

    def _sample_workflow_rules(self) -> str:
        return (
            "- Sample workflow/status lives on public.sample_headers.status.\n"
            "- Active sample/batch filtering uses public.sample_headers.isactive = true.\n"
            "- public.sample_details has sample_header_id and analyte_id only for this catalog; it does not have isactive, status, sample_tracking_stage, or created_at.\n"
            "- If public.sample_details is needed, join public.sample_headers ON public.sample_details.sample_header_id = public.sample_headers.id before filtering by header fields.\n"
            "- Common status values: 'Samples In Lab', 'Sample Verification', 'Sample Approval', 'Sample Received', 'Completed', 'Rejected'."
        )

    def _deterministic_sql_fix(self, sql: str, error_msg: str, message: str) -> Optional[str]:
        """
        Recover common sample workflow mistakes from small local models.
        Keeps this narrow: only sample/batch workflow count and breakdown questions.
        """
        m = message.lower()
        err = error_msg.lower()
        sql_lower = sql.lower()

        if not any(word in m for word in ("sample", "samples", "batch", "batches")):
            if self._is_analyst_performance_query(m):
                return self._analyst_performance_sql(m)
            return None

        status = self._workflow_status_from_message(m)
        is_count = any(phrase in m for phrase in ("how many", "count", "number of", "total"))
        wants_breakdown = any(
            phrase in m
            for phrase in ("breakdown", "bar", "chart", "graph", "by status", "by stage", "workflow stage")
        )

        column_scope_error = (
            "column \"isactive\" does not exist" in err
            or "column \"status\" does not exist" in err
            or "column \"sample_tracking_stage\" does not exist" in err
            or "missing from-clause entry" in err
            or "sample_details" in sql_lower
        )

        if not column_scope_error:
            return None

        if wants_breakdown:
            return (
                "SELECT status, COUNT(*) AS cnt "
                "FROM public.sample_headers "
                "WHERE isactive = true "
                "GROUP BY status "
                "ORDER BY cnt DESC "
                f"LIMIT {_MAX_ROWS}"
            )

        if status and is_count:
            return (
                "SELECT COUNT(*) AS n "
                "FROM public.sample_headers "
                f"WHERE isactive = true AND status = '{status}' "
                f"LIMIT {_MAX_ROWS}"
            )

        return None

    def _is_analyst_performance_query(self, message: str) -> bool:
        has_actor = any(word in message for word in ("analyst", "analysts", "staff", "personnel", "team"))
        has_metric = any(word in message for word in ("performance", "rank", "ranking", "top", "best", "workload"))
        return has_actor and has_metric

    def _analyst_performance_sql(self, message: str) -> str:
        limit_match = re.search(r"\btop\s+(\d+)\b", message)
        limit = int(limit_match.group(1)) if limit_match else 10
        limit = max(1, min(limit, _MAX_ROWS))

        if "approval" in message or "approved" in message:
            metric = "batches_approved"
            join_condition = "u.id = sh.approve_user_id"
        elif "verification" in message or "verified" in message:
            metric = "batches_verified"
            join_condition = "u.id = sh.verify_user_id"
        else:
            return self._analyst_composite_performance_sql(limit)

        return (
            f"SELECT u.name, COUNT(sh.id) AS {metric} "
            "FROM public.users u "
            f"JOIN public.sample_headers sh ON {join_condition} "
            "WHERE sh.created_at::timestamptz >= (NOW() - INTERVAL '30 days') "
            "AND u.active = 1 "
            "GROUP BY u.name "
            f"ORDER BY {metric} DESC "
            f"LIMIT {limit}"
        )

    def _analyst_composite_performance_sql(self, limit: int) -> str:
        return (
            "SELECT "
            "u.name, "
            "COALESCE(v.batches_verified, 0) AS batches_verified, "
            "COALESCE(a.batches_approved, 0) AS batches_approved, "
            "("
            "COALESCE(sr.sample_results_recorded, 0) + "
            "COALESCE(cr.control_results_recorded, 0) + "
            "COALESCE(mr.media_results_recorded, 0)"
            ") AS results_recorded, "
            "("
            "COALESCE(v.batches_verified, 0) * 2 + "
            "COALESCE(a.batches_approved, 0) * 3 + "
            "COALESCE(sr.sample_results_recorded, 0) + "
            "COALESCE(cr.control_results_recorded, 0) + "
            "COALESCE(mr.media_results_recorded, 0)"
            ") AS performance_score "
            "FROM public.users u "
            "LEFT JOIN ("
            "SELECT verify_user_id AS analyst_id, COUNT(*) AS batches_verified "
            "FROM public.sample_headers "
            "WHERE verify_user_id IS NOT NULL "
            "AND isactive = true "
            "AND created_at::timestamptz >= (NOW() - INTERVAL '30 days') "
            "GROUP BY verify_user_id"
            ") v ON v.analyst_id = u.id "
            "LEFT JOIN ("
            "SELECT approve_user_id AS analyst_id, COUNT(*) AS batches_approved "
            "FROM public.sample_headers "
            "WHERE approve_user_id IS NOT NULL "
            "AND isactive = true "
            "AND created_at::timestamptz >= (NOW() - INTERVAL '30 days') "
            "GROUP BY approve_user_id"
            ") a ON a.analyst_id = u.id "
            "LEFT JOIN ("
            "SELECT analyst_id, COUNT(*) AS sample_results_recorded "
            "FROM public.track_sample_results "
            "WHERE analyst_id IS NOT NULL "
            "AND recorded_at::timestamptz >= (NOW() - INTERVAL '30 days') "
            "GROUP BY analyst_id"
            ") sr ON sr.analyst_id = u.id "
            "LEFT JOIN ("
            "SELECT analyst_id, COUNT(*) AS control_results_recorded "
            "FROM public.track_control_results "
            "WHERE analyst_id IS NOT NULL "
            "AND recorded_at::timestamptz >= (NOW() - INTERVAL '30 days') "
            "GROUP BY analyst_id"
            ") cr ON cr.analyst_id = u.id "
            "LEFT JOIN ("
            "SELECT analyst_id, COUNT(*) AS media_results_recorded "
            "FROM public.track_media_results "
            "WHERE analyst_id IS NOT NULL "
            "AND recorded_at::timestamptz >= (NOW() - INTERVAL '30 days') "
            "GROUP BY analyst_id"
            ") mr ON mr.analyst_id = u.id "
            "WHERE u.active = 1 "
            "AND ("
            "COALESCE(v.batches_verified, 0) + "
            "COALESCE(a.batches_approved, 0) + "
            "COALESCE(sr.sample_results_recorded, 0) + "
            "COALESCE(cr.control_results_recorded, 0) + "
            "COALESCE(mr.media_results_recorded, 0)"
            ") > 0 "
            "ORDER BY performance_score DESC "
            f"LIMIT {limit}"
        )

    def _workflow_status_from_message(self, message: str) -> Optional[str]:
        workflow_map = [
            (("verification", "verified", "verify"), "Sample Verification"),
            (("approval", "approved", "approve"), "Sample Approval"),
            (("in lab", "laboratory", "analysis in progress"), "Samples In Lab"),
            (("received", "receipt"), "Sample Received"),
            (("completed", "complete"), "Completed"),
            (("rejected", "cancelled", "canceled"), "Rejected"),
        ]

        for keywords, status in workflow_map:
            if any(keyword in message for keyword in keywords):
                return status
        return None

    def _contains_blocked_table(self, sql: str) -> bool:
        sql_lower = sql.lower()
        if re.search(r"\bpublic\.users\b", sql_lower) and re.search(
            r"\b(password|remember_token|two_factor|email|phone|id_number|nssf|nhif|kra_pin|verify_code)\b",
            sql_lower,
        ):
            return True

        for table in BLOCKED_TABLES:
            if table == "users" and re.search(r"\bpublic\.users\b", sql_lower):
                continue
            if re.search(rf"\b{re.escape(table)}\b", sql_lower):
                return True
        for schema in BLOCKED_SCHEMAS:
            if schema in sql_lower:
                return True
        return False

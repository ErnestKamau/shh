"""Database connection manager for the centralized PostgreSQL database."""
from __future__ import annotations

from contextlib import contextmanager
from typing import Generator

import pandas as pd
from loguru import logger
from sqlalchemy import create_engine, text, event
from sqlalchemy.pool import NullPool

from py_etl.config.config import settings


class DatabaseManager:
    """Manages centralized PostgreSQL connections for AI read/index operations."""

    def __init__(self) -> None:
        self.postgres_engine = create_engine(
            settings.postgres_url,
            poolclass=NullPool,
            echo=False,
            connect_args={
                "connect_timeout": 30,
                "options": "-c idle_in_transaction_session_timeout=120000",
            },
        )

        @event.listens_for(self.postgres_engine, "connect")
        def receive_connect(dbapi_conn, connection_record):
            """Check PostgreSQL connection health on connect."""
            try:
                cursor = dbapi_conn.cursor()
                cursor.execute("SELECT 1")
                cursor.close()
                logger.debug("PostgreSQL connection health check passed")
            except Exception as exc:
                logger.warning(f"PostgreSQL connection health check failed: {exc}")
                raise

        @event.listens_for(self.postgres_engine, "before_cursor_execute")
        def prevent_non_ai_writes(conn, cursor, statement, parameters, context, executemany):
            if self._is_blocked_write(statement):
                raise PermissionError(
                    "Python AI database access is read-only outside the ai schema. "
                    "Use Laravel migrations or application services for operational data changes."
                )

        logger.info("Centralized PostgreSQL database connection initialised")

    # ── Context managers ──────────────────────────────────────────────────

    @contextmanager
    @contextmanager
    def postgres_connection(self) -> Generator:
        """Yield a raw PostgreSQL DBAPI connection (auto-closed on exit)."""
        conn = self.postgres_engine.connect()
        try:
            yield conn
        finally:
            conn.close()

    # ── Extract ───────────────────────────────────────────────────────────

    def extract_from_source(self, query: str) -> pd.DataFrame:
        """
        Run *query* against centralized PostgreSQL.

        Args:
            query: SQL SELECT statement.

        Returns:
            DataFrame containing all result rows.

        Raises:
            Exception: Re-raises any database error after logging.
        """
        try:
            with self.postgres_connection() as conn:
                df = pd.read_sql(query, conn)
                logger.info(f"Extracted {len(df)} rows from PostgreSQL")
                return df
        except Exception as exc:
            logger.error(f"PostgreSQL extraction failed: {exc}")
            raise

    def extract_chunked(
        self,
        table: str,
        primary_key: str = "id",
        chunk_size: int | None = None,
        last_id: int = 0,
    ) -> Generator[pd.DataFrame, None, None]:
        """
        Yield successive DataFrame chunks from *table* ordered by *primary_key*.

        Uses keyset pagination (``WHERE id > last_id``) to avoid OFFSET
        performance issues on large tables.

        Args:
            table:       Source table name.
            primary_key: Column to order and paginate by (default ``id``).
            chunk_size:  Rows per chunk; defaults to ``settings.chunk_size``.
            last_id:     Resume from this id value (exclusive).

        Yields:
            DataFrame chunks.
        """
        size = chunk_size or settings.chunk_size
        current_id = last_id

        quoted_table = self._quote_identifier(table)
        quoted_primary_key = self._quote_identifier(primary_key)

        with self.postgres_connection() as conn:
            while True:
                query = (
                    f"SELECT * FROM {quoted_table} "
                    f"WHERE {quoted_primary_key} > {current_id} "
                    f"ORDER BY {quoted_primary_key} ASC "
                    f"LIMIT {size}"
                )
                df = pd.read_sql(query, conn)

                if df.empty:
                    break

                yield df
                current_id = int(df[primary_key].max())

    # ── Load ─────────────────────────────────────────────────────────────

    def load_to_postgres(
        self,
        df: pd.DataFrame,
        table_name: str,
        schema: str = "ai",
        if_exists: str = "replace",
    ) -> int:
        """
        Write *df* to PostgreSQL table ``schema.table_name``.

        Args:
            df:         DataFrame to persist.
            table_name: Destination table (without schema prefix).
            schema:     Target schema. Python AI writes are restricted to ``ai``.
            if_exists:  ``'replace'``, ``'append'``, or ``'fail'``.

        Returns:
            Number of rows written.

        Raises:
            Exception: Re-raises any database error after logging.
        """
        if schema != settings.ai_schema:
            raise PermissionError("Python AI writes are restricted to the ai schema")

        try:
            with self.postgres_connection() as conn:
                df.to_sql(
                    name=table_name,
                    con=conn,
                    schema=schema,
                    if_exists=if_exists,
                    index=False,
                    method="multi",
                    chunksize=settings.chunk_size,
                )
                logger.info(f"Loaded {len(df)} rows to {schema}.{table_name}")
                return len(df)
        except Exception as exc:
            logger.error(f"PostgreSQL load failed: {exc}")
            raise

    def execute_postgres_sql(self, sql: str) -> None:
        """Execute SQL guarded by the Python AI write policy."""
        try:
            with self.postgres_connection() as conn:
                conn.execute(text(sql))
                conn.commit()
                logger.debug("Executed SQL successfully")
        except Exception as exc:
            logger.error(f"SQL execution failed: {exc}")
            raise

    def truncate_table(self, table_name: str, schema: str = "ai") -> None:
        """Truncate ``schema.table_name`` with CASCADE."""
        if schema != settings.ai_schema:
            raise PermissionError("Python AI writes are restricted to the ai schema")
        self.execute_postgres_sql(f"TRUNCATE TABLE {schema}.{table_name} CASCADE")
        logger.info(f"Truncated {schema}.{table_name}")

    # ── Health check ──────────────────────────────────────────────────────

    def test_connections(self) -> dict:
        """
        Ping both databases and return a status dict.

        Returns:
            ``{'source': bool, 'postgres': bool, 'errors': list[str]}``
        """
        results: dict = {"source": False, "postgres": False, "errors": []}

        try:
            self.test_source_connection()
            results["source"] = True
            logger.info("✓ Source PostgreSQL connection successful")
        except Exception as exc:
            results["errors"].append(f"Source PostgreSQL: {exc}")
            logger.error(f"✗ Source PostgreSQL connection failed: {exc}")

        try:
            self.test_postgres_connection()
            results["postgres"] = True
            logger.info("✓ PostgreSQL connection successful")
        except Exception as exc:
            results["errors"].append(f"PostgreSQL: {exc}")
            logger.error(f"✗ PostgreSQL connection failed: {exc}")

        return results

    def test_source_connection(self) -> None:
        """Ping centralized PostgreSQL source."""
        self.test_postgres_connection()

    def test_postgres_connection(self) -> None:
        """Ping PostgreSQL target."""
        with self.postgres_connection() as conn:
            conn.execute(text("SELECT 1")).fetchone()

    def get_source_schema(self, table: str) -> dict[str, dict]:
        """Fetch operational source table schema from the public schema."""
        return self.get_postgres_schema(table, schema="public")

    def get_postgres_schema(self, table: str, schema: str = "reporting") -> dict[str, dict]:
        """Fetch PostgreSQL table schema from information_schema."""
        sql = """
            SELECT column_name, data_type, is_nullable
            FROM information_schema.columns
            WHERE table_schema = :schema AND table_name = :table
        """
        with self.postgres_connection() as conn:
            rows = conn.execute(text(sql), {"schema": schema, "table": table}).fetchall()
            return {r[0]: {"type": r[1], "nullable": r[2] == "YES"} for r in rows}

    # ── Write guard ───────────────────────────────────────────────────────

    def _is_blocked_write(self, statement: str) -> bool:
        """
        Block Python-initiated writes unless the target is explicitly in ai.

        The database role should enforce this in production. This guard catches
        accidental DML/DDL in the AI service code path during development too.
        """
        sql = " ".join(statement.strip().lower().split())
        if not sql:
            return False

        write_verbs = (
            "insert ",
            "update ",
            "delete ",
            "truncate ",
            "merge ",
            "alter ",
            "drop ",
            "create ",
            "replace ",
        )
        if not sql.startswith(write_verbs):
            return False

        allowed_prefixes = (
            "insert into ai.",
            "update ai.",
            "delete from ai.",
            "truncate table ai.",
            "create table ai.",
            "create table if not exists ai.",
            "create index if not exists ",
            "drop table if exists ai.",
        )
        if sql.startswith(allowed_prefixes):
            if sql.startswith("create index if not exists "):
                return " on ai." not in sql
            return False

        return True

    # ── Cleanup ───────────────────────────────────────────────────────────

    def close(self) -> None:
        """Dispose database engine connection pools."""
        self.postgres_engine.dispose()
        logger.info("Database connection closed")

    @staticmethod
    def _quote_identifier(identifier: str) -> str:
        return '"' + identifier.replace('"', '""') + '"'


# Shared singleton used across Python AI services.
db_manager = DatabaseManager()

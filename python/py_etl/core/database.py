"""
Database Connection Manager (PHASE 0 ENHANCED)
Handles connections to MySQL (source) and PostgreSQL (target).

PHASE 0 Enhancements:
- Connection timeout (30 seconds initial connection)
- Idle transaction timeout (PostgreSQL: 2 minutes)
- Connection health checks
"""
from __future__ import annotations

from contextlib import contextmanager
from typing import Generator

import pandas as pd
from loguru import logger
from sqlalchemy import create_engine, text, event
from sqlalchemy.pool import NullPool

from py_etl.config.config import settings


class DatabaseManager:
    """Manages database connections and operations for the IMARA ETL pipeline (PHASE 0 Enhanced)."""

    def __init__(self) -> None:
        # MySQL Source (IMARA LIMS operational database)
        # PHASE 0: Add connection timeout
        self.mysql_engine = create_engine(
            settings.mysql_url,
            poolclass=NullPool,   # No pooling – ETL processes are short-lived
            echo=False,
            connect_args={
                'connect_timeout': 30,  # 30 seconds initial connection
            },
        )

        # PostgreSQL Target (AI / Reporting repository)
        # PHASE 0: Add connection + idle timeout
        self.postgres_engine = create_engine(
            settings.postgres_url,
            poolclass=NullPool,
            echo=False,
            connect_args={
                'connect_timeout': 30,  # 30 seconds initial connection
                'options': '-c idle_in_transaction_session_timeout=120000',  # 2 min idle
            },
        )
        
        # PHASE 0: Add connection health check (ping on connect)
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

        logger.info("Database connections initialised (PHASE 0: timeouts enabled)")

    # ── Context managers ──────────────────────────────────────────────────

    @contextmanager
    def mysql_connection(self) -> Generator:
        """Yield a raw MySQL DBAPI connection (auto-closed on exit)."""
        conn = self.mysql_engine.connect()
        try:
            yield conn
        finally:
            conn.close()

    @contextmanager
    def postgres_connection(self) -> Generator:
        """Yield a raw PostgreSQL DBAPI connection (auto-closed on exit)."""
        conn = self.postgres_engine.connect()
        try:
            yield conn
        finally:
            conn.close()

    # ── Extract ───────────────────────────────────────────────────────────

    def extract_from_mysql(self, query: str) -> pd.DataFrame:
        """
        Run *query* against MySQL and return a pandas DataFrame.

        Args:
            query: SQL SELECT statement.

        Returns:
            DataFrame containing all result rows.

        Raises:
            Exception: Re-raises any database error after logging.
        """
        try:
            with self.mysql_connection() as conn:
                df = pd.read_sql(query, conn)
                logger.info(f"Extracted {len(df)} rows from MySQL")
                return df
        except Exception as exc:
            logger.error(f"MySQL extraction failed: {exc}")
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
            table:       Source MySQL table name.
            primary_key: Column to order and paginate by (default ``id``).
            chunk_size:  Rows per chunk; defaults to ``settings.etl_batch_size``.
            last_id:     Resume from this id value (exclusive).

        Yields:
            DataFrame chunks.
        """
        size = chunk_size or settings.etl_batch_size
        current_id = last_id

        with self.mysql_connection() as conn:
            while True:
                query = (
                    f"SELECT * FROM `{table}` "
                    f"WHERE `{primary_key}` > {current_id} "
                    f"ORDER BY `{primary_key}` ASC "
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
        schema: str = "staging",
        if_exists: str = "replace",
    ) -> int:
        """
        Write *df* to PostgreSQL table ``schema.table_name``.

        Args:
            df:         DataFrame to persist.
            table_name: Destination table (without schema prefix).
            schema:     Target schema (staging, reporting, ai, vector).
            if_exists:  ``'replace'``, ``'append'``, or ``'fail'``.

        Returns:
            Number of rows written.

        Raises:
            Exception: Re-raises any database error after logging.
        """
        try:
            with self.postgres_connection() as conn:
                df.to_sql(
                    name=table_name,
                    con=conn,
                    schema=schema,
                    if_exists=if_exists,
                    index=False,
                    method="multi",
                    chunksize=settings.etl_batch_size,
                )
                logger.info(f"Loaded {len(df)} rows to {schema}.{table_name}")
                return len(df)
        except Exception as exc:
            logger.error(f"PostgreSQL load failed: {exc}")
            raise

    def execute_postgres_sql(self, sql: str) -> None:
        """Execute arbitrary DDL/DML on PostgreSQL."""
        try:
            with self.postgres_connection() as conn:
                conn.execute(text(sql))
                conn.commit()
                logger.debug("Executed SQL successfully")
        except Exception as exc:
            logger.error(f"SQL execution failed: {exc}")
            raise

    def truncate_table(self, table_name: str, schema: str = "staging") -> None:
        """Truncate ``schema.table_name`` with CASCADE."""
        self.execute_postgres_sql(f"TRUNCATE TABLE {schema}.{table_name} CASCADE")
        logger.info(f"Truncated {schema}.{table_name}")

    # ── Health check ──────────────────────────────────────────────────────

    def test_connections(self) -> dict:
        """
        Ping both databases and return a status dict.

        Returns:
            ``{'mysql': bool, 'postgres': bool, 'errors': list[str]}``
        """
        results: dict = {"mysql": False, "postgres": False, "errors": []}

        try:
            self.test_mysql_connection()
            results["mysql"] = True
            logger.info("✓ MySQL connection successful")
        except Exception as exc:
            results["errors"].append(f"MySQL: {exc}")
            logger.error(f"✗ MySQL connection failed: {exc}")

        try:
            self.test_postgres_connection()
            results["postgres"] = True
            logger.info("✓ PostgreSQL connection successful")
        except Exception as exc:
            results["errors"].append(f"PostgreSQL: {exc}")
            logger.error(f"✗ PostgreSQL connection failed: {exc}")

        return results

    def test_mysql_connection(self) -> None:
        """Ping MySQL source."""
        with self.mysql_connection() as conn:
            conn.execute(text("SELECT 1")).fetchone()

    def test_postgres_connection(self) -> None:
        """Ping PostgreSQL target."""
        with self.postgres_connection() as conn:
            conn.execute(text("SELECT 1")).fetchone()

    def get_mysql_schema(self, table: str) -> dict[str, dict]:
        """Fetch MySQL table schema (column types)."""
        sql = f"DESCRIBE `{table}`"
        with self.mysql_connection() as conn:
            rows = conn.execute(text(sql)).fetchall()
            # DESCRIBE returns: Field, Type, Null, Key, Default, Extra
            return {r[0]: {"type": r[1], "nullable": r[2] == "YES"} for r in rows}

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

    # ── Cleanup ───────────────────────────────────────────────────────────

    def close(self) -> None:
        """Dispose both engine connection pools."""
        self.mysql_engine.dispose()
        self.postgres_engine.dispose()
        logger.info("Database connections closed")


# Shared singleton used across the ETL codebase
db_manager = DatabaseManager()

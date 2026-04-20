from sqlalchemy import text
from loguru import logger

from python.py_etl.core.database import db_manager as pyetl_db_manager
from python.ai_service.config.settings import settings


class DatabaseService:
    def extract_from_mysql(self, query: str):
        return pyetl_db_manager.extract_from_mysql(query)

    def load_to_postgres(self, df, table_name: str, schema: str = "reporting", if_exists: str = "append") -> int:
        return pyetl_db_manager.load_to_postgres(
            df=df,
            table_name=table_name,
            schema=schema,
            if_exists=if_exists,
        )

    def get_all_etl_runs(self, limit: int = 100) -> list[dict]:
        schema = settings.ai_reporting_schema
        sql_sync_runs = f"""
            SELECT id, sync_scope AS job_name, started_at, finished_at AS completed_at,
                   status, rows_synced AS records_processed, error_message
            FROM {schema}.sync_runs
            ORDER BY started_at DESC
            LIMIT :limit
        """

        # Try sync_runs first, then etl_runs — each in its own connection to
        # avoid aborted-transaction state carrying over between attempts.
        for sql in [
            sql_sync_runs,
            f"""
                SELECT id, job_name, started_at, completed_at,
                       status, records_processed, error_message
                FROM {schema}.etl_runs
                ORDER BY started_at DESC
                LIMIT :limit
            """,
        ]:
            try:
                with pyetl_db_manager.postgres_connection() as conn:
                    rows = conn.execute(text(sql), {"limit": limit}).mappings().all()
                    return [dict(r) for r in rows]
            except Exception:
                continue

        logger.warning("Unable to query ETL runs: neither sync_runs nor etl_runs table found")
        return []


# shared instance used by new layers
db_manager = DatabaseService()

def get_ai_db():
    """Utility for raw SQL execution using pgsql_ai connection."""
    return pyetl_db_manager.postgres_engine.connect()

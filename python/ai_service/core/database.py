from python.py_pipeline.core.database import db_manager as pypipeline_db_manager


class DatabaseService:
    def extract_from_source(self, query: str):
        return pypipeline_db_manager.extract_from_source(query)

    def load_to_postgres(self, df, table_name: str, schema: str = "ai", if_exists: str = "append") -> int:
        return pypipeline_db_manager.load_to_postgres(
            df=df,
            table_name=table_name,
            schema=schema,
            if_exists=if_exists,
        )


# shared instance used by new layers
db_manager = DatabaseService()

def get_ai_db():
    """Utility for raw SQL execution using the centralized PostgreSQL connection."""
    return pypipeline_db_manager.postgres_engine.connect()

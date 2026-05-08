"""
Configuration management for IMARA Python AI data access.
Loads database credentials and AI settings from the project root .env,
mirroring the variables defined in Laravel's config/imara_ai.php.
"""
from __future__ import annotations

from pathlib import Path
from functools import lru_cache

from pydantic import Field
from pydantic_settings import BaseSettings, SettingsConfigDict


# Resolve the project root (.env lives three levels above config/)
_ENV_PATH = Path(__file__).resolve().parents[3] / ".env"


class Settings(BaseSettings):
    """
    All settings are read from environment variables (or the root .env).
    Variable names match those already present in the Laravel .env so no
    duplication is needed.
    """

    model_config = SettingsConfigDict(
        env_file=str(_ENV_PATH),
        env_file_encoding="utf-8",
        extra="ignore",
        case_sensitive=False,
    )

    # ── Centralized PostgreSQL database ───────────────────────────────────
    db_host: str = Field("127.0.0.1", alias="DB_HOST")
    db_port: int = Field(5432, alias="DB_PORT")
    db_database: str = Field("gcla", alias="DB_DATABASE")
    db_username: str = Field("root", alias="DB_USERNAME")
    db_password: str = Field("", alias="DB_PASSWORD")

    ai_db_sslmode: str = Field("prefer", alias="AI_DB_SSLMODE")

    # ── Schema names ──────────────────────────────────────────────────────
    ai_reporting_schema: str = Field("reporting", alias="AI_REPORTING_SCHEMA")
    ai_schema: str = Field("ai", alias="AI_SCHEMA")

    # ── Ollama / LLM settings ─────────────────────────────────────────────
    ollama_host: str = Field("http://localhost:11434", alias="OLLAMA_HOST")

    # ── Derived connection URLs ───────────────────────────────────────────
    @property
    def postgres_url(self) -> str:
        return (
            f"postgresql+psycopg2://{self.db_username}:{self.db_password}"
            f"@{self.db_host}:{self.db_port}/{self.db_database}"
            f"?sslmode={self.ai_db_sslmode}"
        )

    @property
    def source_url(self) -> str:
        return self.postgres_url

    @property
    def chunk_size(self) -> int:
        return 500


@lru_cache(maxsize=1)
def get_settings() -> Settings:
    return Settings()


# Module-level shortcut used across Python AI services.
settings = get_settings()

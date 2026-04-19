"""
Configuration management for IMARA Python ETL Engine.
Loads database credentials and ETL settings from the project root .env,
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

    # ── MySQL source ──────────────────────────────────────────────────────
    db_host: str = Field("127.0.0.1", alias="DB_HOST")
    db_port: int = Field(3306, alias="DB_PORT")
    db_database: str = Field("polucon_local", alias="DB_DATABASE")
    db_username: str = Field("root", alias="DB_USERNAME")
    db_password: str = Field("", alias="DB_PASSWORD")

    # ── PostgreSQL AI / Reporting target ─────────────────────────────────
    ai_db_host: str = Field("127.0.0.1", alias="AI_DB_HOST")
    ai_db_port: int = Field(5433, alias="AI_DB_PORT")
    ai_db_database: str = Field("imara_ai", alias="AI_DB_DATABASE")
    ai_db_username: str = Field("root", alias="AI_DB_USERNAME")
    ai_db_password: str = Field("", alias="AI_DB_PASSWORD")
    ai_db_schema: str = Field("public", alias="AI_DB_SCHEMA")
    ai_db_sslmode: str = Field("prefer", alias="AI_DB_SSLMODE")

    # ── Schema names ──────────────────────────────────────────────────────
    ai_reporting_schema: str = Field("reporting", alias="AI_REPORTING_SCHEMA")
    ai_schema: str = Field("ai", alias="AI_SCHEMA")

    # ── ETL settings ──────────────────────────────────────────────────────
    ai_etl_chunk_size: int = Field(500, alias="AI_ETL_CHUNK_SIZE")

    # ── Reporting thresholds (kept for reference / downstream use) ────────
    ai_reporting_qc_cv_warning_pct: float = Field(15.0, alias="AI_REPORTING_QC_CV_WARNING_PCT")
    ai_reporting_qc_cv_critical_pct: float = Field(25.0, alias="AI_REPORTING_QC_CV_CRITICAL_PCT")
    ai_reporting_inventory_near_expiry_days: int = Field(30, alias="AI_REPORTING_INVENTORY_NEAR_EXPIRY_DAYS")
    ai_reporting_document_expiry_warning_days: int = Field(30, alias="AI_REPORTING_DOCUMENT_EXPIRY_WARNING_DAYS")
    ai_reporting_equipment_risk_days_1: int = Field(7, alias="AI_REPORTING_EQUIPMENT_RISK_DAYS_1")
    ai_reporting_equipment_risk_days_2: int = Field(14, alias="AI_REPORTING_EQUIPMENT_RISK_DAYS_2")
    ai_reporting_equipment_risk_days_3: int = Field(30, alias="AI_REPORTING_EQUIPMENT_RISK_DAYS_3")

    # ── Ollama / LLM settings ─────────────────────────────────────────────
    ollama_host: str = Field("http://localhost:11434", alias="OLLAMA_HOST")

    # ── Derived connection URLs ───────────────────────────────────────────
    @property
    def mysql_url(self) -> str:
        return (
            f"mysql+pymysql://{self.db_username}:{self.db_password}"
            f"@{self.db_host}:{self.db_port}/{self.db_database}"
            f"?charset=utf8mb4"
        )

    @property
    def postgres_url(self) -> str:
        return (
            f"postgresql+psycopg2://{self.ai_db_username}:{self.ai_db_password}"
            f"@{self.ai_db_host}:{self.ai_db_port}/{self.ai_db_database}"
            f"?sslmode={self.ai_db_sslmode}"
        )

    @property
    def etl_batch_size(self) -> int:
        return self.ai_etl_chunk_size


@lru_cache(maxsize=1)
def get_settings() -> Settings:
    return Settings()


# Module-level shortcut used across the ETL codebase
settings = get_settings()

"""
QC Robust Statistics Transformer — ISO 13528 Algorithm A
Reads raw qc_results from Postgres, computes per-analyte robust mean and
robust standard deviation using Algorithm A (iterative re-weighting), and
upserts the results into reporting.qc_processed_results.

This runs AFTER qc_results and analytes have been synced from MySQL so it
always produces up-to-date statistics regardless of MySQL's manual processing.
"""
from __future__ import annotations

from typing import Optional

import numpy as np
import pandas as pd
from loguru import logger
from sqlalchemy import text

from py_etl.core.database import DatabaseManager
from py_etl.config.config import settings


class QcRobustStatsTransformer:
    """
    Computes ISO 13528 Algorithm A robust statistics per analyte and writes
    them to reporting.qc_processed_results in Postgres.

    Algorithm A (ISO 13528:2022 §6.5):
      1. Start with median as initial robust mean (x*₀).
      2. Start with 1.4826 * MAD as initial robust SD (s*₀).
      3. For each iteration, cap each value:
         δ = 1.5 * s*  (Huber constant h = 1.5)
         yᵢ = min(max(xᵢ, x* - δ), x* + δ)
      4. Update x* = mean(yᵢ), s* = 1.134 * std(yᵢ)  (correction factor)
      5. Repeat until convergence (change < ε * s*).
    """

    HUBER_H = 1.5          # Huber constant for Algorithm A
    CORRECTION = 1.134     # ISO 13528 SD correction factor
    CONVERGENCE = 1e-6     # Stop when relative change < this
    MAX_ITER = 50

    def __init__(self, db_manager: Optional[DatabaseManager] = None) -> None:
        self.db_manager = db_manager or DatabaseManager()
        self.schema = settings.ai_reporting_schema  # "reporting"

    # ─────────────────────────────────────────────────────────────────────────
    # Public API
    # ─────────────────────────────────────────────────────────────────────────

    def run(self) -> int:
        """
        Full ETL cycle: extract → compute → upsert.
        Returns the number of analyte rows updated.
        """
        logger.info("QcRobustStatsTransformer: starting ISO 13528 Algorithm A computation")
        df = self._extract()
        if df.empty:
            logger.warning("No processed QC results found in Postgres — skipping robust stats")
            return 0

        stats = self._compute_all(df)
        count = self._upsert(stats)
        logger.info(f"QcRobustStatsTransformer: updated robust stats for {count} analytes")
        return count

    # ─────────────────────────────────────────────────────────────────────────
    # Extract
    # ─────────────────────────────────────────────────────────────────────────

    def _extract(self) -> pd.DataFrame:
        """Pull numeric qc_results rows from Postgres."""
        query = f"""
            SELECT
                qr.analyte_id,
                qr.result::NUMERIC AS result_value
            FROM {self.schema}.qc_results qr
            WHERE qr.is_qc_processed = TRUE
              AND qr.result ~ '^-?[0-9]+(\\.[0-9]+)?$'
              AND qr.analyte_id IS NOT NULL
        """
        with self.db_manager.postgres_connection() as conn:
            df = pd.read_sql(text(query), conn)
        logger.info(f"Extracted {len(df)} numeric QC result rows for robust stats")
        return df

    # ─────────────────────────────────────────────────────────────────────────
    # Compute
    # ─────────────────────────────────────────────────────────────────────────

    def _compute_all(self, df: pd.DataFrame) -> list[dict]:
        """Compute Algorithm A stats for every analyte group."""
        stats = []
        for analyte_id, group in df.groupby("analyte_id"):
            values = group["result_value"].dropna().values.astype(float)
            if len(values) < 2:
                continue
            robust_mean, robust_sd = self._algorithm_a(values)
            if robust_mean is None:
                continue
            median = float(np.median(values))
            robust_cv = (robust_sd / robust_mean) if robust_mean != 0 else 0.0
            stats.append({
                "analyte_id":              int(analyte_id),
                "robust_mean":             round(float(robust_mean), 8),
                "robust_standard_deviation": round(float(robust_sd), 8),
                "robust_median":           round(median, 8),
                "robust_cv":               round(robust_cv, 8),
                "robust_cv_percentage":    round(robust_cv * 100, 8),
            })
        return stats

    @classmethod
    def _algorithm_a(cls, values: np.ndarray) -> tuple[Optional[float], Optional[float]]:
        """
        ISO 13528 Algorithm A iterative robust estimation.
        Returns (robust_mean, robust_sd) or (None, None) on failure.
        """
        try:
            x_star = float(np.median(values))
            mad = float(np.median(np.abs(values - x_star)))
            s_star = 1.4826 * mad if mad > 0 else float(np.std(values, ddof=1))
            if s_star == 0:
                return float(np.mean(values)), 0.0

            for _ in range(cls.MAX_ITER):
                delta = cls.HUBER_H * s_star
                y = np.clip(values, x_star - delta, x_star + delta)
                new_mean = float(np.mean(y))
                new_sd = cls.CORRECTION * float(np.std(y, ddof=1))
                if new_sd == 0:
                    break
                if abs(new_mean - x_star) < cls.CONVERGENCE * s_star:
                    x_star, s_star = new_mean, new_sd
                    break
                x_star, s_star = new_mean, new_sd

            return x_star, max(s_star, 0.0)
        except Exception as exc:
            logger.warning(f"Algorithm A failed: {exc}")
            return None, None

    # ─────────────────────────────────────────────────────────────────────────
    # Upsert
    # ─────────────────────────────────────────────────────────────────────────

    def _upsert(self, stats: list[dict]) -> int:
        """
        Upsert per-analyte robust stats into reporting.qc_processed_results.
        Creates a row if no row with matching analyte_id exists; updates otherwise.
        """
        if not stats:
            return 0

        upsert_sql = text(f"""
            INSERT INTO {self.schema}.qc_processed_results
                (source_id, analyte_id, robust_mean, robust_standard_deviation,
                 robust_median, robust_cv, robust_cv_percentage, synced_at)
            VALUES
                (:source_id, :analyte_id, :robust_mean, :robust_standard_deviation,
                 :robust_median, :robust_cv, :robust_cv_percentage, NOW())
            ON CONFLICT (source_id)
            DO UPDATE SET
                robust_mean                = EXCLUDED.robust_mean,
                robust_standard_deviation  = EXCLUDED.robust_standard_deviation,
                robust_median              = EXCLUDED.robust_median,
                robust_cv                  = EXCLUDED.robust_cv,
                robust_cv_percentage       = EXCLUDED.robust_cv_percentage,
                synced_at                  = NOW()
        """)

        with self.db_manager.postgres_connection() as conn:
            for row in stats:
                # Use analyte_id as synthetic source_id for this computed row
                conn.execute(upsert_sql, {**row, "source_id": row["analyte_id"] + 9_000_000})
            conn.commit()

        return len(stats)

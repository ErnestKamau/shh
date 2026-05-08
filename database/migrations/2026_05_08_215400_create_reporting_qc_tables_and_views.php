<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. reporting.analytes
        DB::statement("
            CREATE TABLE IF NOT EXISTS reporting.analytes (
                id                  BIGSERIAL PRIMARY KEY,
                source_id           BIGINT   NOT NULL,
                code                TEXT,
                name                TEXT,
                reporting_unit      TEXT,
                decimal_places      INTEGER,
                is_active           BOOLEAN,
                source_created_at   TEXT,
                source_updated_at   TEXT,
                synced_at           TEXT,
                payload             JSONB,
                CONSTRAINT uq_analytes_source_id UNIQUE (source_id)
            )
        ");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_analytes_source_id ON reporting.analytes (source_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_analytes_code      ON reporting.analytes (code)");

        // 2. reporting.qc_results
        DB::statement("
            CREATE TABLE IF NOT EXISTS reporting.qc_results (
                id                      BIGSERIAL PRIMARY KEY,
                source_id               BIGINT   NOT NULL,
                analyte_id              BIGINT,
                analyte_code            TEXT,
                analyte_processed_id    BIGINT,
                sample_header_id        BIGINT,
                sample_detail_id        BIGINT,
                sample_detail_code      TEXT,
                result                  TEXT,
                status_code             TEXT,
                guide_low               NUMERIC,
                guide_high              NUMERIC,
                unit_code               TEXT,
                is_qc_processed         BOOLEAN,
                source_created_at       TEXT,
                source_updated_at       TEXT,
                synced_at               TEXT,
                payload                 JSONB,
                CONSTRAINT uq_qc_results_source_id UNIQUE (source_id)
            )
        ");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_qc_results_source_id          ON reporting.qc_results (source_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_qc_results_analyte_id         ON reporting.qc_results (analyte_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_qc_results_status_code        ON reporting.qc_results (status_code)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_qc_results_source_created_at  ON reporting.qc_results (source_created_at)");

        // 3. reporting.qc_processed_results
        DB::statement("
            CREATE TABLE IF NOT EXISTS reporting.qc_processed_results (
                id                                          BIGSERIAL PRIMARY KEY,
                source_id                                   BIGINT       NOT NULL,
                sample_type_id                              BIGINT,
                analysis_type_id                            BIGINT,
                analyte_id                                  BIGINT,
                method_id                                   BIGINT,
                standard_id                                 BIGINT,
                standard_value_id                           BIGINT,
                robust_standard_deviation                   NUMERIC,
                robust_mean                                 NUMERIC,
                robust_median                               NUMERIC,
                robust_cv                                   NUMERIC,
                robust_cv_percentage                        NUMERIC,
                source_created_at                           TEXT,
                source_updated_at                           TEXT,
                synced_at                                   TEXT,
                payload                                     JSONB,
                CONSTRAINT uq_qc_processed_results_source_id UNIQUE (source_id)
            )
        ");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_qc_processed_results_source_id ON reporting.qc_processed_results (source_id)");

        // 4. v_qc_stability_metrics
        DB::statement("
            CREATE OR REPLACE VIEW reporting.v_qc_stability_metrics AS
            SELECT
                a.name                                                              AS analyte_name,
                a.code                                                              AS analyte_code,
                a.reporting_unit,
                pr.robust_mean,
                pr.robust_standard_deviation                                        AS robust_sd,
                pr.robust_cv_percentage                                             AS robust_cv_pct,
                (pr.robust_mean + 2 * pr.robust_standard_deviation)                AS ucl,
                (pr.robust_mean - 2 * pr.robust_standard_deviation)                AS lcl,
                COUNT(qr.source_id)                                                 AS total_tests,
                SUM(CASE WHEN qr.status_code = 'PASSED'          THEN 1 ELSE 0 END) AS passed_tests,
                SUM(CASE WHEN qr.status_code IN ('FAILED','OUT_OF_CONTROL')
                                                                 THEN 1 ELSE 0 END) AS failed_tests,
                CASE WHEN COUNT(qr.source_id) > 0
                     THEN ROUND(SUM(CASE WHEN qr.status_code = 'PASSED' THEN 1 ELSE 0 END)::NUMERIC
                                / COUNT(qr.source_id) * 100, 2)
                     ELSE 0 END                                                     AS pass_rate_pct
            FROM reporting.qc_processed_results pr
            JOIN reporting.analytes a ON a.source_id = pr.analyte_id
            LEFT JOIN reporting.qc_results qr
                   ON qr.analyte_id = pr.analyte_id
                  AND qr.is_qc_processed = TRUE
            GROUP BY a.name, a.code, a.reporting_unit,
                     pr.robust_mean, pr.robust_standard_deviation, pr.robust_cv_percentage
        ");

        // 5. v_qc_drift_trends
        DB::statement("
            CREATE OR REPLACE VIEW reporting.v_qc_drift_trends AS
            SELECT
                a.name                                                              AS analyte_name,
                a.code                                                              AS analyte_code,
                LEFT(qr.source_created_at, 10)                                      AS test_date,
                ROUND(AVG(NULLIF(qr.result, '')::NUMERIC), 4)                       AS avg_value,
                ROUND((pr.robust_mean + 2 * pr.robust_standard_deviation)::NUMERIC, 4) AS ucl,
                ROUND((pr.robust_mean - 2 * pr.robust_standard_deviation)::NUMERIC, 4) AS lcl,
                pr.robust_mean                                                      AS center_line,
                COUNT(qr.source_id)                                                 AS test_count,
                CASE WHEN AVG(NULLIF(qr.result, '')::NUMERIC) > (pr.robust_mean + 2 * pr.robust_standard_deviation)
                          OR AVG(NULLIF(qr.result, '')::NUMERIC) < (pr.robust_mean - 2 * pr.robust_standard_deviation)
                     THEN 'OUT_OF_CONTROL'
                     WHEN AVG(NULLIF(qr.result, '')::NUMERIC) > (pr.robust_mean + pr.robust_standard_deviation)
                          OR AVG(NULLIF(qr.result, '')::NUMERIC) < (pr.robust_mean - pr.robust_standard_deviation)
                     THEN 'WARNING'
                     ELSE 'IN_CONTROL' END                                          AS control_status
            FROM reporting.qc_results qr
            JOIN reporting.qc_processed_results pr ON pr.analyte_id = qr.analyte_id
            JOIN reporting.analytes a              ON a.source_id   = qr.analyte_id
            WHERE qr.is_qc_processed = TRUE
              AND qr.result ~ '^[0-9]+\.?[0-9]*$'
            GROUP BY a.name, a.code, LEFT(qr.source_created_at, 10),
                     pr.robust_mean, pr.robust_standard_deviation
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS reporting.v_qc_drift_trends");
        DB::statement("DROP VIEW IF EXISTS reporting.v_qc_stability_metrics");
        DB::statement("DROP TABLE IF EXISTS reporting.qc_processed_results");
        DB::statement("DROP TABLE IF EXISTS reporting.qc_results");
        DB::statement("DROP TABLE IF EXISTS reporting.analytes");
    }
};

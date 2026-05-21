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
        // 1. public.v_qc_stability_metrics
        DB::statement("
            CREATE OR REPLACE VIEW public.v_qc_stability_metrics AS
            SELECT
                a.name                                                              AS analyte_name,
                a.code                                                              AS analyte_code,
                a.reporting_unit,
                pr.robust_mean,
                pr.robust_standard_deviation                                        AS robust_sd,
                pr.robust_cv_percentage                                             AS robust_cv_pct,
                (pr.robust_mean + 2 * pr.robust_standard_deviation)                AS ucl,
                (pr.robust_mean - 2 * pr.robust_standard_deviation)                AS lcl,
                COUNT(qr.id)                                                        AS total_tests,
                SUM(CASE WHEN qr.status_code = 'PASSED'          THEN 1 ELSE 0 END) AS passed_tests,
                SUM(CASE WHEN qr.status_code IN ('FAILED','OUT_OF_CONTROL')
                                                                 THEN 1 ELSE 0 END) AS failed_tests,
                CASE WHEN COUNT(qr.id) > 0
                     THEN ROUND(SUM(CASE WHEN qr.status_code = 'PASSED' THEN 1 ELSE 0 END)::NUMERIC
                                / COUNT(qr.id) * 100, 2)
                     ELSE 0 END                                                     AS pass_rate_pct
            FROM public.qc_processed_result pr
            JOIN public.analytes a ON a.id = pr.analyte_id
            LEFT JOIN public.qc_results qr
                   ON qr.analyte_id = pr.analyte_id
                  AND qr.is_qc_processed = TRUE
            GROUP BY a.name, a.code, a.reporting_unit,
                     pr.robust_mean, pr.robust_standard_deviation, pr.robust_cv_percentage
        ");

        // 2. public.v_qc_drift_trends
        DB::statement("
            CREATE OR REPLACE VIEW public.v_qc_drift_trends AS
            SELECT
                a.name                                                              AS analyte_name,
                a.code                                                              AS analyte_code,
                TO_CHAR(qr.created_at, 'YYYY-MM-DD')                                AS test_date,
                ROUND(AVG(NULLIF(qr.result, '')::NUMERIC), 4)                       AS avg_value,
                ROUND((pr.robust_mean + 2 * pr.robust_standard_deviation)::NUMERIC, 4) AS ucl,
                ROUND((pr.robust_mean - 2 * pr.robust_standard_deviation)::NUMERIC, 4) AS lcl,
                pr.robust_mean                                                      AS center_line,
                COUNT(qr.id)                                                        AS test_count,
                CASE WHEN AVG(NULLIF(qr.result, '')::NUMERIC) > (pr.robust_mean + 2 * pr.robust_standard_deviation)
                          OR AVG(NULLIF(qr.result, '')::NUMERIC) < (pr.robust_mean - 2 * pr.robust_standard_deviation)
                     THEN 'OUT_OF_CONTROL'
                     WHEN AVG(NULLIF(qr.result, '')::NUMERIC) > (pr.robust_mean + pr.robust_standard_deviation)
                          OR AVG(NULLIF(qr.result, '')::NUMERIC) < (pr.robust_mean - pr.robust_standard_deviation)
                     THEN 'WARNING'
                     ELSE 'IN_CONTROL' END                                          AS control_status
            FROM public.qc_results qr
            JOIN public.qc_processed_result pr ON pr.analyte_id = qr.analyte_id
            JOIN public.analytes a              ON a.id   = qr.analyte_id
            WHERE qr.is_qc_processed = TRUE
              AND qr.result ~ '^[0-9]+\.?[0-9]*$'
            GROUP BY a.name, a.code, TO_CHAR(qr.created_at, 'YYYY-MM-DD'),
                     pr.robust_mean, pr.robust_standard_deviation
        ");

        // 3. public.v_qc_pareto_analysis
        DB::statement("
            CREATE OR REPLACE VIEW public.v_qc_pareto_analysis AS
            WITH failure_counts AS (
                SELECT
                    a.name          AS analyte_name,
                    a.code          AS analyte_code,
                    qr.status_code  AS failure_reason,
                    COUNT(*)        AS failure_count
                FROM public.qc_results qr
                JOIN public.analytes a ON a.id = qr.analyte_id
                WHERE qr.status_code IN ('FAILED', 'OUT_OF_CONTROL')
                  AND qr.is_qc_processed = TRUE
                GROUP BY a.name, a.code, qr.status_code
            )
            SELECT 
                *,
                SUM(failure_count) OVER(PARTITION BY analyte_name ORDER BY failure_count DESC) AS cumulative_failure_count,
                SUM(failure_count) OVER(PARTITION BY analyte_name) AS total_failures
            FROM failure_counts
        ");

        // 4. public.v_qc_ooc_events
        DB::statement("
            CREATE OR REPLACE VIEW public.v_qc_ooc_events AS
            SELECT
                qr.id                           AS source_id,
                a.name                          AS analyte_name,
                a.code                          AS analyte_code,
                qr.result,
                pr.robust_mean                  AS target_value,
                pr.robust_standard_deviation    AS robust_sd,
                qr.status_code                  AS event_type,
                TO_CHAR(qr.created_at, 'YYYY-MM-DD HH24:MI:SS') AS event_time,
                qr.result                       AS remark
            FROM public.qc_results qr
            JOIN public.analytes a ON a.id = qr.analyte_id
            JOIN public.qc_processed_result pr ON pr.analyte_id = qr.analyte_id
            WHERE qr.status_code = 'OUT_OF_CONTROL'
              AND qr.is_qc_processed = TRUE
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS public.v_qc_ooc_events");
        DB::statement("DROP VIEW IF EXISTS public.v_qc_pareto_analysis");
        DB::statement("DROP VIEW IF EXISTS public.v_qc_drift_trends");
        DB::statement("DROP VIEW IF EXISTS public.v_qc_stability_metrics");
    }
};

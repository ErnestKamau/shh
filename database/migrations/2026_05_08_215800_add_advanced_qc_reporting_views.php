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
        // 1. v_qc_pareto_analysis
        DB::statement("
            CREATE OR REPLACE VIEW reporting.v_qc_pareto_analysis AS
            WITH failure_counts AS (
                SELECT
                    a.name          AS analyte_name,
                    a.code          AS analyte_code,
                    qr.status_code  AS failure_reason,
                    COUNT(*)        AS failure_count
                FROM reporting.qc_results qr
                JOIN reporting.analytes a ON a.source_id = qr.analyte_id
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

        // 2. v_qc_ooc_events
        DB::statement("
            CREATE OR REPLACE VIEW reporting.v_qc_ooc_events AS
            SELECT
                qr.source_id,
                a.name          AS analyte_name,
                a.code          AS analyte_code,
                qr.result,
                pr.robust_mean  AS target_value,
                pr.robust_standard_deviation AS robust_sd,
                qr.status_code  AS event_type,
                qr.source_created_at AS event_time,
                qr.payload->>'remark' AS remark
            FROM reporting.qc_results qr
            JOIN reporting.analytes a ON a.source_id = qr.analyte_id
            JOIN reporting.qc_processed_results pr ON pr.analyte_id = qr.analyte_id
            WHERE qr.status_code = 'OUT_OF_CONTROL'
              AND qr.is_qc_processed = TRUE
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS reporting.v_qc_ooc_events");
        DB::statement("DROP VIEW IF EXISTS reporting.v_qc_pareto_analysis");
    }
};

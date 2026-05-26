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
        // Fix analyst_id type in tat_captured
        DB::statement("ALTER TABLE tat_captured ALTER COLUMN analyst_id TYPE uuid USING analyst_id::text::uuid");

        // 1. tat_captured_view
        DB::statement("
            CREATE OR REPLACE VIEW tat_captured_view AS 
            SELECT 
                tc.id AS id,
                tc.created_at AS created_at,
                tc.updated_at AS updated_at,
                tc.captured_result_id AS captured_result_id,
                tc.analysis_type_id AS analysis_type_id,
                tc.analyte_id AS analyte_id,
                tc.sample_type_id AS sample_type_id,
                tc.sample_detail_id AS sample_detail_id,
                tc.result AS result,
                tc.analyst_id AS analyst_id,
                tc.tat_overdue_days AS tat_overdue_days,
                tc.tat_date AS tat_date,
                tc.finished_date AS finished_date,
                tc.is_complete AS is_complete,
                tc.sample_header_id AS sample_header_id,
                tc.tat_remark AS tat_remark,
                tc.start_date_analysis AS start_date_analysis,
                u.name AS analyst_name,
                u.email AS analyst_email,
                at2.name AS analysis_type_name,
                a.name AS analyte_name,
                a.code AS analyte_code,
                st.name AS sample_type_name,
                sd.sample_code AS sample_code,
                sh.receipt_date AS receipt_date 
            FROM tat_captured tc
            JOIN users u ON tc.analyst_id = u.id
            JOIN sample_headers sh ON tc.sample_header_id = sh.id
            JOIN captured_results cr ON tc.captured_result_id = cr.id
            JOIN analysis_types at2 ON tc.analysis_type_id = at2.id
            JOIN analytes a ON tc.analyte_id = a.id
            JOIN sample_types st ON tc.sample_type_id = st.id
            JOIN sample_details sd ON tc.sample_detail_id = sd.id
        ");

        // 2. v_lab_tat_stage_summary
        // Overdue = CURRENT_TIMESTAMP > date_expected + 12h grace (matches signedTatOffsetDays rule)
        // Due today = deadline is within the next 12 h (inside grace window)
        DB::statement("
            CREATE OR REPLACE VIEW v_lab_tat_stage_summary AS
            SELECT 
                sh.status AS workflow_stage,
                COUNT(sh.id) AS total_batches,
                GREATEST(
                    COUNT(sh.id) - SUM(CASE WHEN CURRENT_TIMESTAMP > (sh.date_expected + INTERVAL '12 hours') THEN 1 ELSE 0 END),
                    0
                ) AS completed_batches,
                SUM(CASE WHEN CURRENT_TIMESTAMP > (sh.date_expected + INTERVAL '12 hours') THEN 1 ELSE 0 END) AS overdue_batches,
                SUM(CASE WHEN CURRENT_TIMESTAMP BETWEEN sh.date_expected - INTERVAL '12 hours'
                                              AND     sh.date_expected + INTERVAL '12 hours' THEN 1 ELSE 0 END) AS due_today_batches,
                AVG(EXTRACT(EPOCH FROM (sh.date_expected - sh.created_at)) / 86400) AS avg_days_to_target,
                AVG(CASE WHEN CURRENT_TIMESTAMP > (sh.date_expected + INTERVAL '12 hours')
                         THEN EXTRACT(EPOCH FROM (CURRENT_TIMESTAMP - sh.date_expected)) / 86400
                         ELSE NULL END) AS avg_days_overdue,
                (SELECT AVG(completed.updated_at::date - completed.created_at::date) 
                 FROM sample_headers completed 
                 WHERE completed.status = 'Completed' AND completed.isactive = true) AS avg_completion_days,
                MAX(sh.updated_at) AS refreshed_at
            FROM sample_headers sh
            WHERE sh.isactive = true 
              AND sh.status IS NOT NULL 
              AND sh.status != '' 
              AND sh.status != 'Received'
              AND sh.status != 'Reports'
              AND sh.status != 'Completed'
            GROUP BY sh.status
        ");

        // 3. v_lab_tat_aging_buckets
        // Buckets use hour offsets with 12h grace so a batch 13h past deadline = '1_3_overdue'
        // and a batch only 11h past deadline = 'due_today' (within grace window).
        DB::statement("
            CREATE OR REPLACE VIEW v_lab_tat_aging_buckets AS
            SELECT 
                CASE
                    WHEN date_expected IS NULL
                        THEN 'no_target'
                    WHEN CURRENT_TIMESTAMP > (date_expected + INTERVAL '8 days' + INTERVAL '12 hours')
                        THEN '8_plus_overdue'
                    WHEN CURRENT_TIMESTAMP > (date_expected + INTERVAL '4 days' + INTERVAL '12 hours')
                        THEN '4_7_overdue'
                    WHEN CURRENT_TIMESTAMP > (date_expected + INTERVAL '12 hours')
                        THEN '1_3_overdue'
                    WHEN CURRENT_TIMESTAMP BETWEEN (date_expected - INTERVAL '12 hours')
                                           AND     (date_expected + INTERVAL '12 hours')
                        THEN 'due_today'
                    ELSE 'on_time'
                END AS aging_bucket,
                COUNT(id) AS batch_count
            FROM sample_headers
            WHERE isactive = true 
              AND status IS NOT NULL 
              AND status != '' 
              AND status != 'Received'
              AND status != 'Reports'
              AND status != 'Completed'
            GROUP BY aging_bucket
        ");

        // 4. v_lab_tat_overdue_batches
        // Only lists batches past their deadline by more than 12h (grace-aware).
        // days_overdue is the net time past the grace window expressed in days (decimal).
        // Must DROP first because column type changes from integer → numeric.
        DB::statement("DROP VIEW IF EXISTS v_lab_tat_overdue_batches");
        DB::statement("
            CREATE VIEW v_lab_tat_overdue_batches AS
            SELECT 
                sh.id AS source_id,
                sh.batch_code,
                sh.status AS workflow_stage,
                sh.date_expected AS target_date,
                ROUND(
                    EXTRACT(EPOCH FROM (CURRENT_TIMESTAMP - (sh.date_expected + INTERVAL '12 hours'))) / 86400.0,
                    1
                ) AS days_overdue,
                CASE WHEN st.name LIKE '%QC%' OR st.name LIKE '%QA%' THEN 1 ELSE 0 END AS is_qc_batch
            FROM sample_headers sh
            LEFT JOIN sample_types st ON st.id = sh.sample_type_id
            WHERE sh.isactive = true 
              AND sh.status IS NOT NULL 
              AND sh.status != '' 
              AND sh.status != 'Received'
              AND sh.status != 'Reports'
              AND sh.status != 'Completed'
              AND CURRENT_TIMESTAMP > (sh.date_expected + INTERVAL '12 hours')
        ");

        // 5. Backfill tat_overdue_days to signed convention.
        //
        // The old CapturedObserver wrote tat_overdue_days as an *unsigned* positive
        // integer for both early and late completions; direction was encoded in
        // tat_remark (1-3 = early, 4-5 = late).
        //
        // New signed convention (matches DashboardHelpers::computeSignedTatOffset):
        //   tat_overdue_days > 0  → finished late  (past deadline beyond 12 h grace)
        //   tat_overdue_days = 0  → on-time        (within ±12 h grace window)
        //   tat_overdue_days < 0  → finished early (before deadline beyond 12 h grace)
        //
        // Negate rows where tat_remark IN (1,2,3) AND tat_overdue_days > 0.
        // Those are early completions stored as positive — flip them to negative.
        // Rows at 0 and late rows (remark 4/5) are already correct in both schemes.
        DB::statement("
            UPDATE tat_captured
            SET tat_overdue_days = -tat_overdue_days
            WHERE tat_remark IN (1, 2, 3)
              AND tat_overdue_days > 0
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Undo the signed backfill: restore negated values to their original positive state
        DB::statement("
            UPDATE tat_captured
            SET tat_overdue_days = ABS(tat_overdue_days)
            WHERE tat_overdue_days < 0
        ");

        DB::statement("DROP VIEW IF EXISTS v_lab_tat_overdue_batches");
        DB::statement("DROP VIEW IF EXISTS v_lab_tat_aging_buckets");
        DB::statement("DROP VIEW IF EXISTS v_lab_tat_stage_summary");
        DB::statement("DROP VIEW IF EXISTS tat_captured_view");
    }
};

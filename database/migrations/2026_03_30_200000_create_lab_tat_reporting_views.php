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
        // 1. v_lab_tat_stage_summary
        // We explicitly join sample_analysis_stages or filter empty/received states
        // to strictly present active technical workflow load.
        DB::statement("
            CREATE OR REPLACE VIEW v_lab_tat_stage_summary AS
            SELECT 
                sh.status AS workflow_stage,
                COUNT(sh.id) AS total_batches,
                SUM(CASE WHEN DATEDIFF(CURRENT_DATE, COALESCE(sh.date_expected, CURRENT_DATE)) > 0 THEN 1 ELSE 0 END) AS overdue_batches,
                SUM(CASE WHEN sh.date_expected = CURRENT_DATE THEN 1 ELSE 0 END) AS due_today_batches,
                AVG(DATEDIFF(COALESCE(sh.date_expected, CURRENT_DATE), sh.created_at)) AS avg_days_to_target,
                AVG(CASE WHEN DATEDIFF(CURRENT_DATE, sh.date_expected) > 0 THEN DATEDIFF(CURRENT_DATE, sh.date_expected) ELSE NULL END) AS avg_days_overdue,
                (SELECT AVG(DATEDIFF(completed.updated_at, completed.created_at)) 
                 FROM sample_headers completed 
                 WHERE completed.status = 'Completed' AND completed.isactive = 1) AS avg_completion_days,
                MAX(sh.updated_at) AS refreshed_at
            FROM sample_headers sh
            WHERE sh.isactive = 1 
              AND sh.status IS NOT NULL 
              AND sh.status != '' 
              AND sh.status != 'Received'
              AND sh.status != 'Reports'
              AND sh.status != 'Completed'
            GROUP BY sh.status
        ");

        // 2. v_lab_tat_aging_buckets
        DB::statement("
            CREATE OR REPLACE VIEW v_lab_tat_aging_buckets AS
            SELECT 
                CASE
                    WHEN date_expected IS NULL THEN 'no_target'
                    WHEN DATEDIFF(CURRENT_DATE, date_expected) >= 8 THEN '8_plus_overdue'
                    WHEN DATEDIFF(CURRENT_DATE, date_expected) >= 4 THEN '4_7_overdue'
                    WHEN DATEDIFF(CURRENT_DATE, date_expected) >= 1 THEN '1_3_overdue'
                    WHEN DATEDIFF(CURRENT_DATE, date_expected) = 0 THEN 'due_today'
                    ELSE 'on_time'
                END AS aging_bucket,
                COUNT(id) AS batch_count
            FROM sample_headers
            WHERE isactive = 1 
              AND status IS NOT NULL 
              AND status != '' 
              AND status != 'Received'
              AND status != 'Reports'
              AND status != 'Completed'
            GROUP BY aging_bucket
        ");

        // 3. v_lab_tat_overdue_batches
        // robustly identifies QC batches based on sample_type naming conventions 
        // to adapt since 'qc_type_id' isn't explicitly active on the core table layout
        DB::statement("
            CREATE OR REPLACE VIEW v_lab_tat_overdue_batches AS
            SELECT 
                sh.id AS source_id,
                sh.batch_code,
                sh.status AS workflow_stage,
                sh.date_expected AS target_date,
                DATEDIFF(CURRENT_DATE, sh.date_expected) AS days_overdue,
                CASE WHEN st.name LIKE '%QC%' OR st.name LIKE '%QA%' THEN 1 ELSE 0 END AS is_qc_batch
            FROM sample_headers sh
            LEFT JOIN sample_types st ON st.id = sh.sample_type_id
            WHERE sh.isactive = 1 
              AND sh.status IS NOT NULL 
              AND sh.status != '' 
              AND sh.status != 'Received'
              AND sh.status != 'Reports'
              AND sh.status != 'Completed'
              AND sh.date_expected < CURRENT_DATE
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_lab_tat_overdue_batches");
        DB::statement("DROP VIEW IF EXISTS v_lab_tat_aging_buckets");
        DB::statement("DROP VIEW IF EXISTS v_lab_tat_stage_summary");
    }
};

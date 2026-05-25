<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_lab_tat_stage_summary");

        DB::statement("
            CREATE VIEW v_lab_tat_stage_summary AS
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
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_lab_tat_stage_summary");

        DB::statement("
            CREATE VIEW v_lab_tat_stage_summary AS
            SELECT
                sh.status AS workflow_stage,
                COUNT(sh.id) AS total_batches,
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
    }
};

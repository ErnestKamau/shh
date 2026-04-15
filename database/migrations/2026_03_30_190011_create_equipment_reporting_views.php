<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Identification of latest logs per equipment
        DB::statement("
            CREATE OR REPLACE VIEW v_equipment_last_logs AS
            SELECT 
                e.id as equipment_id,
                m.max_maint_date,
                c.max_calib_date
            FROM equipment e
            LEFT JOIN (
                SELECT equipment_id, MAX(date) as max_maint_date 
                FROM maintainance_calibration_logs 
                WHERE type = 'maintainance' GROUP BY equipment_id
            ) m ON m.equipment_id = e.id
            LEFT JOIN (
                SELECT equipment_id, MAX(date) as max_calib_date 
                FROM maintainance_calibration_logs 
                WHERE type = 'calibration' GROUP BY equipment_id
            ) c ON c.equipment_id = e.id
        ");

        // 2. Comprehensive Reliability metrics
        DB::statement("
            CREATE OR REPLACE VIEW v_equipment_reliability AS
            SELECT 
                e.id as equipment_id,
                e.name as equipment_name,
                e.asset_code,
                e.assigned_department,
                e.maintainance_days as maintenance_frequency_days,
                e.calibration_days as calibration_cycle_days,
                COALESCE(l.max_maint_date, e.date_purchased) as last_maintenance_date,
                COALESCE(l.max_calib_date, e.date_purchased) as last_calibration_date,
                DATE_ADD(COALESCE(l.max_maint_date, e.date_purchased), INTERVAL e.maintainance_days DAY) as next_maintenance_due,
                DATE_ADD(COALESCE(l.max_calib_date, e.date_purchased), INTERVAL e.calibration_days DAY) as next_calibration_due,
                DATEDIFF(CURDATE(), DATE_ADD(COALESCE(l.max_maint_date, e.date_purchased), INTERVAL e.maintainance_days DAY)) as maintenance_overdue_days,
                DATEDIFF(CURDATE(), DATE_ADD(COALESCE(l.max_calib_date, e.date_purchased), INTERVAL e.calibration_days DAY)) as calibration_overdue_days,
                CASE 
                    WHEN DATEDIFF(DATE_ADD(COALESCE(l.max_maint_date, e.date_purchased), INTERVAL e.maintainance_days DAY), CURDATE()) < 0 THEN 'overdue'
                    WHEN DATEDIFF(DATE_ADD(COALESCE(l.max_maint_date, e.date_purchased), INTERVAL e.maintainance_days DAY), CURDATE()) <= e.maintainance_notification_in_days THEN 'warning'
                    ELSE 'stable'
                END as maintenance_status,
                CASE 
                    WHEN DATEDIFF(DATE_ADD(COALESCE(l.max_calib_date, e.date_purchased), INTERVAL e.calibration_days DAY), CURDATE()) < 0 THEN 'overdue'
                    WHEN DATEDIFF(DATE_ADD(COALESCE(l.max_calib_date, e.date_purchased), INTERVAL e.calibration_days DAY), CURDATE()) <= e.calibration_notification_in_days THEN 'warning'
                    ELSE 'stable'
                END as calibration_status,
                CASE 
                   WHEN DATEDIFF(CURDATE(), DATE_ADD(COALESCE(l.max_maint_date, e.date_purchased), INTERVAL e.maintainance_days DAY)) > 0 
                   OR DATEDIFF(CURDATE(), DATE_ADD(COALESCE(l.max_calib_date, e.date_purchased), INTERVAL e.calibration_days DAY)) > 0 THEN 1 ELSE 0 
                END as is_overdue,
                e.status,
                'PASSED' as verification_status, -- Placeholder
                0 as days_since_maintenance, -- Placeholder or add logic
                e.updated_at
            FROM equipment e
            LEFT JOIN v_equipment_last_logs l ON l.equipment_id = e.id
        ");

        // 3. Departmental Summary Board
        DB::statement("
            CREATE OR REPLACE VIEW v_equipment_summary AS
            SELECT 
                assigned_department as department,
                COUNT(*) as total_assets,
                SUM(CASE WHEN maintenance_status = 'overdue' THEN 1 ELSE 0 END) as maint_overdue,
                SUM(CASE WHEN calibration_status = 'overdue' THEN 1 ELSE 0 END) as calib_overdue,
                SUM(CASE WHEN (DATEDIFF(next_maintenance_due, CURDATE()) BETWEEN 0 AND 30) OR (DATEDIFF(next_calibration_due, CURDATE()) BETWEEN 0 AND 30) THEN 1 ELSE 0 END) as due_soon,
                MAX(updated_at) as refreshed_at
            FROM v_equipment_reliability
            GROUP BY assigned_department
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_equipment_summary");
        DB::statement("DROP VIEW IF EXISTS v_equipment_reliability");
        DB::statement("DROP VIEW IF EXISTS v_equipment_last_logs");
    }
};

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
        // 1. Latest logs per equipment view
        DB::statement("
            CREATE OR REPLACE VIEW v_equipment_last_logs AS
            SELECT 
                e.id as equipment_id,
                m.max_maint_date,
                c.max_calib_date
            FROM public.equipment e
            LEFT JOIN (
                SELECT equipment_id, MAX(date) as max_maint_date 
                FROM public.maintainance_calibration_logs 
                WHERE type IN ('maintenance', 'maintainance') GROUP BY equipment_id
            ) m ON m.equipment_id = e.id
            LEFT JOIN (
                SELECT equipment_id, MAX(date) as max_calib_date 
                FROM public.maintainance_calibration_logs 
                WHERE type = 'calibration' GROUP BY equipment_id
            ) c ON c.equipment_id = e.id
        ");

        // 2. Reliability Metrics View
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
                (COALESCE(l.max_maint_date, e.date_purchased) + (e.maintainance_days * INTERVAL '1 day'))::date as next_maintenance_due,
                (COALESCE(l.max_calib_date, e.date_purchased) + (e.calibration_days * INTERVAL '1 day'))::date as next_calibration_due,
                (CURRENT_DATE - (COALESCE(l.max_maint_date, e.date_purchased) + (e.maintainance_days * INTERVAL '1 day'))::date) as maintenance_overdue_days,
                (CURRENT_DATE - (COALESCE(l.max_calib_date, e.date_purchased) + (e.calibration_days * INTERVAL '1 day'))::date) as calibration_overdue_days,
                CASE 
                    WHEN ((COALESCE(l.max_maint_date, e.date_purchased) + (e.maintainance_days * INTERVAL '1 day'))::date - CURRENT_DATE) < 0 THEN 'overdue'
                    WHEN ((COALESCE(l.max_maint_date, e.date_purchased) + (e.maintainance_days * INTERVAL '1 day'))::date - CURRENT_DATE) <= e.maintainance_notification_in_days THEN 'warning'
                    ELSE 'stable'
                END as maintenance_status,
                CASE 
                    WHEN ((COALESCE(l.max_calib_date, e.date_purchased) + (e.calibration_days * INTERVAL '1 day'))::date - CURRENT_DATE) < 0 THEN 'overdue'
                    WHEN ((COALESCE(l.max_calib_date, e.date_purchased) + (e.calibration_days * INTERVAL '1 day'))::date - CURRENT_DATE) <= e.calibration_notification_in_days THEN 'warning'
                    ELSE 'stable'
                END as calibration_status,
                CASE 
                   WHEN (CURRENT_DATE - (COALESCE(l.max_maint_date, e.date_purchased) + (e.maintainance_days * INTERVAL '1 day'))::date) > 0 
                   OR (CURRENT_DATE - (COALESCE(l.max_calib_date, e.date_purchased) + (e.calibration_days * INTERVAL '1 day'))::date) > 0 THEN 1 ELSE 0 
                END as is_overdue,
                e.status,
                'PASSED' as verification_status,
                0 as days_since_maintenance,
                e.updated_at
            FROM public.equipment e
            LEFT JOIN v_equipment_last_logs l ON l.equipment_id = e.id
        ");

        // 3. Departmental Summary Board View
        DB::statement("
            CREATE OR REPLACE VIEW v_equipment_summary AS
            SELECT 
                assigned_department as department,
                COUNT(*)::integer as total_assets,
                SUM(CASE WHEN maintenance_status = 'overdue' THEN 1 ELSE 0 END)::integer as maint_overdue,
                SUM(CASE WHEN calibration_status = 'overdue' THEN 1 ELSE 0 END)::integer as calib_overdue,
                SUM(CASE WHEN (((next_maintenance_due - CURRENT_DATE) BETWEEN 0 AND 30) OR ((next_calibration_due - CURRENT_DATE) BETWEEN 0 AND 30)) THEN 1 ELSE 0 END)::integer as due_soon,
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
        DB::statement("DROP VIEW IF EXISTS v_equipment_summary CASCADE");
        DB::statement("DROP VIEW IF EXISTS v_equipment_reliability CASCADE");
        DB::statement("DROP VIEW IF EXISTS v_equipment_last_logs CASCADE");
    }
};

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
        // 1. Create trigger function for sample headers
        DB::unprepared("
            CREATE OR REPLACE FUNCTION public.trg_fn_sync_sample_headers()
            RETURNS TRIGGER AS \$\$
            DECLARE
                _payload JSONB;
                _error_msg TEXT;
                _error_detail TEXT;
            BEGIN
                _payload := to_jsonb(NEW);

                BEGIN
                    INSERT INTO reporting.sample_headers (
                        source_id, batch_code, status, crm_customer_id, verify_user_id, 
                        approve_user_id, created_by, is_qc_batch, isactive, processing_date, 
                        approval_date_at, approval_date_raw, sample_tracking_stage, 
                        source_created_at, source_updated_at, synced_at, payload
                    )
                    VALUES (
                        NEW.id, NEW.batch_code, NEW.status, NEW.crm_customer_id, NEW.verify_user_id, 
                        NEW.approve_user_id, NEW.created_by, NEW.is_qc_batch, NEW.isactive, NEW.processing_date, 
                        NEW.approval_date_at, NEW.approval_date_raw, NEW.sample_tracking_stage, 
                        NEW.created_at, NEW.updated_at, NOW(), _payload
                    )
                    ON CONFLICT (source_id) DO UPDATE SET
                        batch_code = EXCLUDED.batch_code,
                        status = EXCLUDED.status,
                        crm_customer_id = EXCLUDED.crm_customer_id,
                        verify_user_id = EXCLUDED.verify_user_id,
                        approve_user_id = EXCLUDED.approve_user_id,
                        is_qc_batch = EXCLUDED.is_qc_batch,
                        isactive = EXCLUDED.isactive,
                        processing_date = EXCLUDED.processing_date,
                        approval_date_at = EXCLUDED.approval_date_at,
                        approval_date_raw = EXCLUDED.approval_date_raw,
                        sample_tracking_stage = EXCLUDED.sample_tracking_stage,
                        source_updated_at = EXCLUDED.source_updated_at,
                        synced_at = NOW(),
                        payload = EXCLUDED.payload;

                    INSERT INTO reporting.sync_index_state (table_key, last_sync_at, rows_synced_last_run, sync_status, updated_at)
                    VALUES ('samples', NOW(), 1, 'SUCCESS', NOW())
                    ON CONFLICT (table_key) DO UPDATE SET
                        last_sync_at = NOW(),
                        rows_synced_last_run = COALESCE(reporting.sync_index_state.rows_synced_last_run, 0) + 1,
                        sync_status = 'SUCCESS',
                        updated_at = NOW();

                EXCEPTION WHEN OTHERS THEN
                    GET STACKED DIAGNOSTICS 
                        _error_msg = MESSAGE_TEXT,
                        _error_detail = PG_EXCEPTION_DETAIL;

                    INSERT INTO reporting.sync_quarantine (run_id, source_table, raw_payload, validation_error, quarantine_reason, created_at)
                    VALUES (NULL, 'samples', to_jsonb(NEW), _error_msg, COALESCE(_error_detail, 'Real-time validation error'), NOW());

                    INSERT INTO reporting.sync_index_state (table_key, last_sync_at, sync_status, sync_error, updated_at)
                    VALUES ('samples', NOW(), 'WARNING_QUARANTINE', _error_msg, NOW())
                    ON CONFLICT (table_key) DO UPDATE SET
                        sync_status = 'WARNING_QUARANTINE',
                        sync_error = EXCLUDED.sync_error,
                        updated_at = NOW();
                END;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // 2. Create trigger function for equipment
        DB::unprepared("
            CREATE OR REPLACE FUNCTION public.trg_fn_sync_equipment()
            RETURNS TRIGGER AS \$\$
            DECLARE
                _payload JSONB;
                _error_msg TEXT;
                _error_detail TEXT;
            BEGIN
                _payload := to_jsonb(NEW);

                BEGIN
                    INSERT INTO reporting.equipment_assets (
                        source_id, name, equipment_number, status, assigned_department, 
                        assigned_employee_id, date_purchased, maintainance_days, 
                        maintainance_notification_in_days, calibration_days, 
                        calibration_notification_in_days, verification_days, warranty_date, 
                        is_disposal, active, asset_type_id, asset_location_id, 
                        source_created_at, source_updated_at, synced_at, payload
                    )
                    VALUES (
                        NEW.id, NEW.name, NEW.equipment_number, NEW.status, NEW.assigned_department, 
                        NEW.assigned_employee_id, NEW.date_purchased, NEW.maintainance_days, 
                        NEW.maintainance_notification_in_days, NEW.calibration_days, 
                        NEW.calibration_notification_in_days, NEW.verification_days, NEW.warranty_date, 
                        NEW.is_disposal, NEW.active, NEW.asset_type_id, NEW.asset_location_id, 
                        NEW.created_at, NEW.updated_at, NOW(), _payload
                    )
                    ON CONFLICT (source_id) DO UPDATE SET
                        name = EXCLUDED.name,
                        equipment_number = EXCLUDED.equipment_number,
                        status = EXCLUDED.status,
                        assigned_department = EXCLUDED.assigned_department,
                        assigned_employee_id = EXCLUDED.assigned_employee_id,
                        maintainance_days = EXCLUDED.maintainance_days,
                        calibration_days = EXCLUDED.calibration_days,
                        active = EXCLUDED.active,
                        source_updated_at = EXCLUDED.source_updated_at,
                        synced_at = NOW(),
                        payload = EXCLUDED.payload;

                    INSERT INTO reporting.sync_index_state (table_key, last_sync_at, rows_synced_last_run, sync_status, updated_at)
                    VALUES ('equipment', NOW(), 1, 'SUCCESS', NOW())
                    ON CONFLICT (table_key) DO UPDATE SET
                        last_sync_at = NOW(),
                        rows_synced_last_run = COALESCE(reporting.sync_index_state.rows_synced_last_run, 0) + 1,
                        sync_status = 'SUCCESS',
                        updated_at = NOW();

                EXCEPTION WHEN OTHERS THEN
                    GET STACKED DIAGNOSTICS _error_msg = MESSAGE_TEXT, _error_detail = PG_EXCEPTION_DETAIL;

                    INSERT INTO reporting.sync_quarantine (run_id, source_table, raw_payload, validation_error, quarantine_reason, created_at)
                    VALUES (NULL, 'equipment', to_jsonb(NEW), _error_msg, COALESCE(_error_detail, 'Equipment real-time validation error'), NOW());
                END;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // 3. Register Triggers
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_sync_sample_headers_realtime ON public.sample_headers;
            CREATE TRIGGER trg_sync_sample_headers_realtime
            AFTER INSERT OR UPDATE ON public.sample_headers
            FOR EACH ROW EXECUTE FUNCTION public.trg_fn_sync_sample_headers();
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_sync_equipment_realtime ON public.equipment;
            CREATE TRIGGER trg_sync_equipment_realtime
            AFTER INSERT OR UPDATE ON public.equipment
            FOR EACH ROW EXECUTE FUNCTION public.trg_fn_sync_equipment();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS trg_sync_sample_headers_realtime ON public.sample_headers;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_sync_equipment_realtime ON public.equipment;");
        DB::unprepared("DROP FUNCTION IF EXISTS public.trg_fn_sync_sample_headers();");
        DB::unprepared("DROP FUNCTION IF EXISTS public.trg_fn_sync_equipment();");
    }
};

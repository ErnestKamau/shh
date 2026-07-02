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
        DB::statement("
            CREATE OR REPLACE VIEW public.qc_results_view AS
            SELECT
                qr.id,
                qr.captured_result_id,
                qr.sample_detail_id,
                qr.sample_header_id,
                qr.sample_detail_code,
                qr.analyte_id,
                qr.analyte_code,
                qr.result,
                qr.remarks,
                qr.is_qc_processed,
                qr.analysis_type_id,
                qr.qc_type_id,
                qr.qc_scheme_id,
                qr.status_code,
                qr.guide,
                qr.standard_value,
                qr.created_at,
                qr.updated_at,
                sh.receipt_date,
                sh.batch_code,
                NULLIF(TRIM(BOTH FROM sh.sample_type_id), '')::uuid AS sample_type_id,
                st.name AS sample_type_name,
                COALESCE(NULLIF(cr.main_value, ''), NULLIF(qr.guide, ''), qr.standard_value) AS main_value,
                COALESCE(analyst_user.name, operator_user.name) AS analyst_name,
                ''::text AS previous_result,
                ''::text AS config_percentage
            FROM public.qc_results qr
            INNER JOIN public.sample_headers sh ON sh.id = qr.sample_header_id
            LEFT JOIN public.sample_types st ON st.id = NULLIF(TRIM(BOTH FROM sh.sample_type_id), '')::uuid
            LEFT JOIN public.captured_results cr ON cr.id = qr.captured_result_id
            LEFT JOIN public.users analyst_user ON analyst_user.id = cr.user_id
            LEFT JOIN public.users operator_user ON operator_user.id = cr.operator_id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS public.qc_results_view');
    }
};

<?php

namespace App\Services\Sampleworkflow;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SamplesByCategoryViewService
{
    public function ensureExists(): void
    {
        try {
            DB::table('samples_by_category')->limit(1)->exists();
        } catch (\Throwable) {
            $this->recreate();
        }
    }

    /**
     * Recreate the view so batches remain visible when CRM customer rows are missing.
     */
    public function recreate(): void
    {
        try {
            DB::statement($this->viewSql());
        } catch (\Throwable $exception) {
            Log::error('Failed to recreate samples_by_category view: '.$exception->getMessage());
        }
    }

    private function viewSql(): string
    {
        return <<<'SQL'
CREATE OR REPLACE VIEW samples_by_category AS
SELECT
    sh.batch_code,
    sh.receipt_date,
    sh.date_collected,
    sh.crm_customer_id,
    sh.sample_type_id,
    sh.reference_number,
    sh.status AS workflow_stage,
    sh.is_routine,
    sh.priority,
    sh.batch_scope,
    sh.customer_survey,
    sh.approval_date,
    sh.submit_by,
    sh.sampled_by_company_personnel,
    sh.radio_active_levels AS batch_no,
    sh.description AS product_description,
    sh.batch_instructions,
    sh.sampling_officer_name,
    sh.retention_date,
    sh.kra_office_ref,
    cc.code AS crm_code,
    cc.name AS crm_name,
    cc.postal_address,
    cc.physical_address,
    sh.crm_unit_name,
    st.code AS sample_type_code,
    st.name AS sample_type_name,
    sd.id,
    sd.sample_code,
    sd.analysis_type_id,
    sd.sample_condition_id,
    sd.barcode,
    sd.comments,
    sd.gps,
    sd.photo_url,
    sd.created_at,
    sd.updated_at,
    sd.sample_header_id,
    sd.sample_point_id,
    sd.company_product_id,
    sd.main_body,
    sd.header_body,
    sd.is_ammendment,
    sd.ammendment_number,
    sd.main_standard,
    sd.secondary_standard,
    sd.short_code,
    sd.material_status,
    sd.third_standard_id,
    sd.sample_no,
    sd.no_of_samples,
    sd.no_of_pots_plants,
    sd.standard_tests,
    sd.compartiment_lot,
    sd.coa_number,
    sd.results,
    sd.lab_sub_no,
    sd.store_id,
    sd.store_slot_id,
    sd.quantity,
    sd.reporting_unit_id,
    sd.mfg_date,
    sd.expiry_date,
    sd.batch_lot_no,
    sd.coa_number_target,
    sd.disposal_date,
    sd.is_disposed,
    sd.notes_body,
    sd.report_number,
    cp.name AS product_name,
    sp.name AS sample_point_name,
    NULL::text AS sample_point_area_name,
    sc.name AS sample_condition_name,
    smain.code AS main_standard_code,
    ssec.code AS sec_standard_code,
    sthird.code AS third_standard_code,
    iss.name AS store_slot_name,
    is2.name AS store_name,
    ru.name AS reporting_unit_name,
    ci.invoice_number,
    am.name AS sampling_method_name,
    am.code AS sampling_method_code,
    l.code AS main_lab_code,
    l.name AS main_lab_name,
    l.id AS main_lab_id,
    ccu.name AS customer_crm_unit
FROM sample_headers sh
JOIN sample_details sd ON sh.id = sd.sample_header_id
LEFT JOIN crm_customers cc ON NULLIF(TRIM(sh.crm_customer_id), '')::uuid = cc.id
JOIN sample_types st ON NULLIF(TRIM(sh.sample_type_id), '')::uuid = st.id
LEFT JOIN company_products cp ON sd.company_product_id = cp.id
LEFT JOIN sample_conditions sc ON sd.sample_condition_id = sc.id
LEFT JOIN sample_points sp ON sd.sample_point_id = sp.id
LEFT JOIN crm_company_units ccu ON NULLIF(TRIM(sh.crm_unit_id), '')::uuid = ccu.id
LEFT JOIN standards smain ON sd.main_standard = smain.id
LEFT JOIN standards ssec ON sd.secondary_standard = ssec.id
LEFT JOIN standards sthird ON sd.third_standard_id = sthird.id
LEFT JOIN inventory_stores is2 ON sd.store_id = is2.id
LEFT JOIN inventory_store_slots iss ON sd.store_slot_id = iss.id
LEFT JOIN reporting_units ru ON sd.reporting_unit_id = ru.id
LEFT JOIN customer_invoice ci ON NULLIF(TRIM(sh.invoice_id), '')::uuid = ci.id
LEFT JOIN analysis_methods am ON NULLIF(TRIM(sh.sampling_method_id), '')::uuid = am.id
LEFT JOIN labs l ON sd.lab_id = l.id
SQL;
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * UUID reference columns on sample_headers that should store string identifiers
     * without PostgreSQL uuid casting errors (e.g. empty strings during sync).
     *
     * @var list<string>
     */
    private array $uuidReferenceColumns = [
        'submission_form_instance_id',
        'crm_customer_id',
        'sample_type_id',
        'receiving_officer',
        'sampling_officer',
        'specialist_analyst_id',
        'invoice_id',
        'verify_user_id',
        'approve_user_id',
        'quote_id',
        'crm_unit_id',
        'company_sub_unit_id',
        'qc_type_id',
        'qc_scheme_id',
        'crm_contact_id',
        'lab_id',
        'sample_header_staging_id',
        'sampling_method_id',
        'zone_id',
        'processing_zone_id',
        'reporting_zone_id',
        'sample_tracking_stage',
    ];

    /**
     * @var list<string>
     */
    private array $dependentViews = [
        'samples_by_category',
        'samples_inter_lab_log_view',
        'samples_to_analysis_relation_view',
        'tat_captured_view',
        'v_lab_tat_aging_buckets',
        'v_lab_tat_overdue_batches',
        'v_lab_tat_stage_summary',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('sample_headers')) {
            return;
        }

        foreach ($this->dependentViews as $view) {
            DB::statement('DROP VIEW IF EXISTS '.$this->quoteIdentifier($view).' CASCADE');
        }

        $this->dropForeignKeysOnColumns('sample_headers', $this->uuidReferenceColumns);

        foreach ($this->uuidReferenceColumns as $column) {
            if (! Schema::hasColumn('sample_headers', $column)) {
                continue;
            }

            DB::statement(sprintf(
                'ALTER TABLE sample_headers ALTER COLUMN %s TYPE VARCHAR(36) USING %s::text',
                $this->quoteIdentifier($column),
                $this->quoteIdentifier($column),
            ));
        }

        $this->recreateDependentViews();
    }

    public function down(): void
    {
        if (! Schema::hasTable('sample_headers')) {
            return;
        }

        foreach ($this->dependentViews as $view) {
            DB::statement('DROP VIEW IF EXISTS '.$this->quoteIdentifier($view).' CASCADE');
        }

        foreach ($this->uuidReferenceColumns as $column) {
            if (! Schema::hasColumn('sample_headers', $column)) {
                continue;
            }

            DB::statement(sprintf(
                "ALTER TABLE sample_headers ALTER COLUMN %s TYPE UUID USING (CASE WHEN %s IS NULL OR %s = '' THEN NULL WHEN %s ~ '^[0-9a-fA-F-]{36}$' THEN %s::uuid ELSE NULL END)",
                $this->quoteIdentifier($column),
                $this->quoteIdentifier($column),
                $this->quoteIdentifier($column),
                $this->quoteIdentifier($column),
                $this->quoteIdentifier($column),
            ));
        }
    }

    private function recreateDependentViews(): void
    {
        DB::statement(<<<'SQL'
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
JOIN crm_customers cc ON NULLIF(TRIM(sh.crm_customer_id), '')::uuid = cc.id
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
SQL);

        DB::statement(<<<'SQL'
CREATE OR REPLACE VIEW samples_inter_lab_log_view AS
SELECT
    sil.id,
    sil.created_at,
    sil.updated_at,
    sil.sample_id,
    sil.to_lab_section_id,
    sil.from_lab_section_id,
    sil.quantity,
    sil.submited_by,
    sil.date_submitted,
    sil.received_by,
    sil.date_received,
    sil.remarks,
    sil.expected_date,
    sil.status,
    sd.sample_code,
    sd.analysis_type_id,
    sd.lab_id,
    sh.sample_type_id,
    sh.id AS sample_header_id,
    sh.batch_code,
    sh.status AS batch_status,
    st.name AS sample_type_name,
    l.name AS to_lab_name,
    l.code AS to_lab_code,
    l2.name AS from_lab_name,
    l2.code AS from_lab_code,
    u2.name AS received_by_name,
    u.name AS submitted_by_name
FROM sample_interlab_log sil
JOIN sample_details sd ON sil.sample_id::text = sd.id::text
JOIN sample_headers sh ON sd.sample_header_id::text = sh.id::text
LEFT JOIN sample_types st ON NULLIF(TRIM(sh.sample_type_id), '')::uuid = st.id
LEFT JOIN sample_analysis_stages l ON sil.to_lab_section_id::text = l.id::text
LEFT JOIN sample_analysis_stages l2 ON sil.from_lab_section_id::text = l2.id::text
LEFT JOIN users u ON sil.submited_by::text = u.id::text
LEFT JOIN users u2 ON sil.received_by::text = u2.id::text
SQL);

        DB::statement(<<<'SQL'
CREATE OR REPLACE VIEW samples_to_analysis_relation_view AS
SELECT
    satr.id,
    satr.created_at,
    satr.updated_at,
    satr.batch_id,
    satr.sample_detail_id,
    satr.analysis_type_id,
    NULL::text AS analyst_ids,
    NULL::uuid AS allocated_by,
    at.lab_section_id AS "Labsection_id",
    sd.sample_code,
    at.name AS analysis_type_name,
    at.level AS analysis_level,
    at.brand_id,
    at.is_pesticide,
    at.result_expo,
    sas.name AS lab_section_name,
    sh.id AS sample_header_id,
    ''::text AS ob_number,
    ''::text AS cr_number,
    ''::text AS enquiry_no,
    l.id AS sample_lab,
    l.name AS sample_lab_name,
    COALESCE(l.code, ''::character varying) AS sample_lab_code,
    ''::text AS allocator
FROM sample_analysis_type_relation satr
JOIN sample_details sd ON sd.id = satr.sample_detail_id
JOIN analysis_types at ON at.id = satr.analysis_type_id
LEFT JOIN sample_analysis_stages sas ON sas.id = at.lab_section_id
JOIN sample_headers sh ON sh.id::text = satr.batch_id::text
LEFT JOIN labs l ON l.id = at.lab_id
SQL);

        DB::statement(<<<'SQL'
CREATE OR REPLACE VIEW tat_captured_view AS
SELECT
    tc.id,
    tc.created_at,
    tc.updated_at,
    tc.captured_result_id,
    tc.analysis_type_id,
    tc.analyte_id,
    tc.sample_type_id,
    tc.sample_detail_id,
    tc.result,
    tc.analyst_id,
    tc.tat_overdue_days,
    tc.tat_date,
    tc.finished_date,
    tc.is_complete,
    tc.sample_header_id,
    tc.tat_remark,
    tc.start_date_analysis,
    u.name AS analyst_name,
    u.email AS analyst_email,
    at2.name AS analysis_type_name,
    a.name AS analyte_name,
    a.code AS analyte_code,
    st.name AS sample_type_name,
    sd.sample_code,
    sh.receipt_date
FROM tat_captured tc
JOIN users u ON tc.analyst_id = u.id
JOIN sample_headers sh ON tc.sample_header_id::text = sh.id::text
JOIN captured_results cr ON tc.captured_result_id = cr.id
JOIN analysis_types at2 ON tc.analysis_type_id = at2.id
JOIN analytes a ON tc.analyte_id = a.id
JOIN sample_types st ON tc.sample_type_id = st.id
JOIN sample_details sd ON tc.sample_detail_id = sd.id
SQL);

        DB::statement(<<<'SQL'
CREATE OR REPLACE VIEW v_lab_tat_aging_buckets AS
SELECT
    CASE
        WHEN sample_headers.date_expected IS NULL THEN 'no_target'::text
        WHEN (CURRENT_DATE - sample_headers.date_expected) >= 8 THEN '8_plus_overdue'::text
        WHEN (CURRENT_DATE - sample_headers.date_expected) >= 4 THEN '4_7_overdue'::text
        WHEN (CURRENT_DATE - sample_headers.date_expected) >= 1 THEN '1_3_overdue'::text
        WHEN sample_headers.date_expected = CURRENT_DATE THEN 'due_today'::text
        ELSE 'on_time'::text
    END AS aging_bucket,
    count(sample_headers.id) AS batch_count
FROM sample_headers
WHERE sample_headers.isactive = true
  AND sample_headers.status IS NOT NULL
  AND sample_headers.status::text <> ''::text
  AND sample_headers.status::text <> 'Received'::text
  AND sample_headers.status::text <> 'Reports'::text
  AND sample_headers.status::text <> 'Completed'::text
GROUP BY (
    CASE
        WHEN sample_headers.date_expected IS NULL THEN 'no_target'::text
        WHEN (CURRENT_DATE - sample_headers.date_expected) >= 8 THEN '8_plus_overdue'::text
        WHEN (CURRENT_DATE - sample_headers.date_expected) >= 4 THEN '4_7_overdue'::text
        WHEN (CURRENT_DATE - sample_headers.date_expected) >= 1 THEN '1_3_overdue'::text
        WHEN sample_headers.date_expected = CURRENT_DATE THEN 'due_today'::text
        ELSE 'on_time'::text
    END
)
SQL);

        DB::statement(<<<'SQL'
CREATE OR REPLACE VIEW v_lab_tat_overdue_batches AS
SELECT
    sh.id AS source_id,
    sh.batch_code,
    sh.status AS workflow_stage,
    sh.date_expected AS target_date,
    CURRENT_DATE - sh.date_expected AS days_overdue,
    CASE
        WHEN st.name::text ~~ '%QC%'::text OR st.name::text ~~ '%QA%'::text THEN 1
        ELSE 0
    END AS is_qc_batch
FROM sample_headers sh
LEFT JOIN sample_types st ON st.id = NULLIF(TRIM(sh.sample_type_id), '')::uuid
WHERE sh.isactive = true
  AND sh.status IS NOT NULL
  AND sh.status::text <> ''::text
  AND sh.status::text <> 'Received'::text
  AND sh.status::text <> 'Reports'::text
  AND sh.status::text <> 'Completed'::text
  AND sh.date_expected < CURRENT_DATE
SQL);

        DB::statement(<<<'SQL'
CREATE OR REPLACE VIEW v_lab_tat_stage_summary AS
SELECT
    sh.status AS workflow_stage,
    count(sh.id) AS total_batches,
    sum(
        CASE
            WHEN CURRENT_DATE > COALESCE(sh.date_expected, CURRENT_DATE) THEN 1
            ELSE 0
        END) AS overdue_batches,
    sum(
        CASE
            WHEN sh.date_expected = CURRENT_DATE THEN 1
            ELSE 0
        END) AS due_today_batches,
    avg(COALESCE(sh.date_expected, CURRENT_DATE) - sh.created_at::date) AS avg_days_to_target,
    avg(
        CASE
            WHEN CURRENT_DATE > sh.date_expected THEN CURRENT_DATE - sh.date_expected
            ELSE NULL::integer
        END) AS avg_days_overdue,
    (
        SELECT avg(completed.updated_at::date - completed.created_at::date)
        FROM sample_headers completed
        WHERE completed.status::text = 'Completed'::text
          AND completed.isactive = true
    ) AS avg_completion_days,
    max(sh.updated_at) AS refreshed_at
FROM sample_headers sh
WHERE sh.isactive = true
  AND sh.status IS NOT NULL
  AND sh.status::text <> ''::text
  AND sh.status::text <> 'Received'::text
  AND sh.status::text <> 'Reports'::text
  AND sh.status::text <> 'Completed'::text
GROUP BY sh.status
SQL);
    }

    /**
     * @param  list<string>  $columns
     */
    private function dropForeignKeysOnColumns(string $table, array $columns): void
    {
        if ($columns === []) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($columns), '?'));

        $constraints = DB::select(
            "SELECT tc.constraint_name
             FROM information_schema.table_constraints tc
             JOIN information_schema.key_column_usage kcu
               ON tc.constraint_name = kcu.constraint_name
              AND tc.table_schema = kcu.table_schema
             WHERE tc.table_schema = current_schema()
               AND tc.table_name = ?
               AND tc.constraint_type = 'FOREIGN KEY'
               AND kcu.column_name IN ({$placeholders})",
            array_merge([$table], $columns),
        );

        foreach ($constraints as $constraint) {
            DB::statement(sprintf(
                'ALTER TABLE %s DROP CONSTRAINT IF EXISTS %s',
                $this->quoteIdentifier($table),
                $this->quoteIdentifier($constraint->constraint_name),
            ));
        }
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '"'.str_replace('"', '""', $identifier).'"';
    }
};

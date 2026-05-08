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
        DB::statement('DROP VIEW IF EXISTS samples_inter_lab_log_view');

        DB::statement(<<<'SQL'
CREATE VIEW samples_inter_lab_log_view AS
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
LEFT JOIN sample_types st ON sh.sample_type_id::text = st.id::text
LEFT JOIN sample_analysis_stages l ON sil.to_lab_section_id::text = l.id::text
LEFT JOIN sample_analysis_stages l2 ON sil.from_lab_section_id::text = l2.id::text
LEFT JOIN users u ON sil.submited_by::text = u.id::text
LEFT JOIN users u2 ON sil.received_by::text = u2.id::text
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS samples_inter_lab_log_view');
    }
};

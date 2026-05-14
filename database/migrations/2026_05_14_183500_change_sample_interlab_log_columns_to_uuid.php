<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop view that depends on these columns
        DB::statement('DROP VIEW IF EXISTS samples_inter_lab_log_view');

        Schema::table('sample_interlab_log', function (Blueprint $table) {
            // In PostgreSQL, changing from integer to UUID requires explicit casting
            DB::statement('ALTER TABLE sample_interlab_log ALTER COLUMN sample_id TYPE UUID USING sample_id::text::uuid');
            DB::statement('ALTER TABLE sample_interlab_log ALTER COLUMN submited_by TYPE UUID USING submited_by::text::uuid');
            DB::statement('ALTER TABLE sample_interlab_log ALTER COLUMN received_by TYPE UUID USING received_by::text::uuid');
            DB::statement('ALTER TABLE sample_interlab_log ALTER COLUMN from_lab_section_id TYPE UUID USING from_lab_section_id::text::uuid');
            DB::statement('ALTER TABLE sample_interlab_log ALTER COLUMN to_lab_section_id TYPE UUID USING to_lab_section_id::text::uuid');
        });

        // Recreate the view
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

        Schema::table('sample_interlab_log', function (Blueprint $table) {
            DB::statement('ALTER TABLE sample_interlab_log ALTER COLUMN sample_id TYPE INTEGER USING sample_id::text::integer');
            DB::statement('ALTER TABLE sample_interlab_log ALTER COLUMN submited_by TYPE INTEGER USING submited_by::text::integer');
            DB::statement('ALTER TABLE sample_interlab_log ALTER COLUMN received_by TYPE INTEGER USING received_by::text::integer');
            DB::statement('ALTER TABLE sample_interlab_log ALTER COLUMN from_lab_section_id TYPE INTEGER USING from_lab_section_id::text::integer');
            DB::statement('ALTER TABLE sample_interlab_log ALTER COLUMN to_lab_section_id TYPE INTEGER USING to_lab_section_id::text::integer');
        });

        // Recreate the view (original version)
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
};

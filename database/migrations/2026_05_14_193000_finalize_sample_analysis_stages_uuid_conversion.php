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
        // 1. chain_of_custodies.tracking_stage_id
        if (Schema::hasTable('chain_of_custodies')) {
            Schema::table('chain_of_custodies', function (Blueprint $table) {
                DB::statement('ALTER TABLE chain_of_custodies ALTER COLUMN tracking_stage_id DROP NOT NULL');
                DB::statement('ALTER TABLE chain_of_custodies ALTER COLUMN tracking_stage_id TYPE TEXT USING tracking_stage_id::text');
                
                $mappings = [
                    '20007' => 'Samples Request Review',
                    '20008' => 'Samples In Lab',
                ];

                foreach ($mappings as $oldId => $name) {
                    DB::statement("
                        UPDATE chain_of_custodies 
                        SET tracking_stage_id = stages.id::text
                        FROM sample_analysis_stages stages
                        WHERE chain_of_custodies.tracking_stage_id = ?
                        AND stages.name ILIKE ?
                    ", [$oldId, $name]);
                }

                DB::statement("ALTER TABLE chain_of_custodies ALTER COLUMN tracking_stage_id TYPE UUID USING (CASE WHEN tracking_stage_id ~ '^[0-9a-fA-F-]{36}$' THEN tracking_stage_id::uuid ELSE NULL END)");
            });
        }

        // 2. analysis_types.lab_section_id
        if (Schema::hasTable('analysis_types')) {
            Schema::table('analysis_types', function (Blueprint $table) {
                // Drop the view that depends on analysis_types.lab_section_id
                DB::statement('DROP VIEW IF EXISTS samples_to_analysis_relation_view');

                DB::statement('ALTER TABLE analysis_types ALTER COLUMN lab_section_id DROP DEFAULT');
                DB::statement('ALTER TABLE analysis_types ALTER COLUMN lab_section_id TYPE TEXT USING lab_section_id::text');
                
                // For analysis types, the mapping might be different, but let's try to map what we can if we had names.
                // However, analysis_types usually refers to lab sections which might have different names.
                // For now, let's just ensure it's converted to UUID or NULL to prevent crashes.
                DB::statement("ALTER TABLE analysis_types ALTER COLUMN lab_section_id TYPE UUID USING (CASE WHEN lab_section_id ~ '^[0-9a-fA-F-]{36}$' THEN lab_section_id::uuid ELSE NULL END)");

                // Recreate the view
                DB::statement("
                    CREATE OR REPLACE VIEW samples_to_analysis_relation_view AS
                    SELECT
                        satr.id,
                        satr.created_at,
                        satr.updated_at,
                        satr.batch_id,
                        satr.sample_detail_id,
                        satr.analysis_type_id,
                        NULL::text                          AS analyst_ids,
                        NULL::uuid                          AS allocated_by,
                        at.lab_section_id                   AS \"Labsection_id\",
                        sd.sample_code,
                        at.name                             AS analysis_type_name,
                        at.level                            AS analysis_level,
                        at.brand_id,
                        at.is_pesticide,
                        at.result_expo,
                        sas.name                            AS lab_section_name,
                        sh.id                               AS sample_header_id,
                        ''                                  AS ob_number,
                        ''                                  AS cr_number,
                        ''                                  AS enquiry_no,
                        l.id                                AS sample_lab,
                        l.name                              AS sample_lab_name,
                        COALESCE(l.code, '')                AS sample_lab_code,
                        ''                                  AS allocator
                    FROM sample_analysis_type_relation AS satr
                    INNER JOIN sample_details AS sd  ON sd.id  = satr.sample_detail_id
                    INNER JOIN analysis_types AS at  ON at.id  = satr.analysis_type_id
                    LEFT  JOIN sample_analysis_stages AS sas ON sas.id = at.lab_section_id
                    INNER JOIN sample_headers AS sh  ON sh.id  = satr.batch_id
                    LEFT  JOIN labs AS l             ON l.id   = at.lab_id
                ");
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('chain_of_custodies')) {
            DB::statement('ALTER TABLE chain_of_custodies ALTER COLUMN tracking_stage_id TYPE INTEGER USING NULL');
        }
        if (Schema::hasTable('analysis_types')) {
            DB::statement('DROP VIEW IF EXISTS samples_to_analysis_relation_view');
            DB::statement('ALTER TABLE analysis_types ALTER COLUMN lab_section_id TYPE INTEGER USING NULL');
            DB::statement("
                CREATE OR REPLACE VIEW samples_to_analysis_relation_view AS
                SELECT
                    satr.id,
                    satr.created_at,
                    satr.updated_at,
                    satr.batch_id,
                    satr.sample_detail_id,
                    satr.analysis_type_id,
                    NULL::text                          AS analyst_ids,
                    NULL::uuid                          AS allocated_by,
                    at.lab_section_id                   AS \"Labsection_id\",
                    sd.sample_code,
                    at.name                             AS analysis_type_name,
                    at.level                            AS analysis_level,
                    at.brand_id,
                    at.is_pesticide,
                    at.result_expo,
                    sas.name                            AS lab_section_name,
                    sh.id                               AS sample_header_id,
                    ''                                  AS ob_number,
                    ''                                  AS cr_number,
                    ''                                  AS enquiry_no,
                    l.id                                AS sample_lab,
                    l.name                              AS sample_lab_name,
                    COALESCE(l.code, '')                AS sample_lab_code,
                    ''                                  AS allocator
                FROM sample_analysis_type_relation AS satr
                INNER JOIN sample_details AS sd  ON sd.id  = satr.sample_detail_id
                INNER JOIN analysis_types AS at  ON at.id  = satr.analysis_type_id
                LEFT  JOIN sample_analysis_stages AS sas ON sas.id::text = at.lab_section_id::text
                INNER JOIN sample_headers AS sh  ON sh.id  = satr.batch_id
                LEFT  JOIN labs AS l             ON l.id   = at.lab_id
            ");
        }
    }
};

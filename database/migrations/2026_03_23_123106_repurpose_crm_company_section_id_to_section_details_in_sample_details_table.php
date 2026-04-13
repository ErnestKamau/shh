<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('sample_details', 'crm_company_section_id')) {
            // Repurpose FK column into plain text.
            // We drop FK constraint + its supporting index first; then we do a raw CHANGE to rename + alter type.
            try {
                DB::statement('ALTER TABLE `sample_details` DROP FOREIGN KEY `sample_details_crm_company_section_id_foreign`');
            } catch (\Throwable) {
                // Ignore if FK already dropped / name differs.
            }

            // MySQL requires key length when changing indexed columns to TEXT.
            try {
                DB::statement('ALTER TABLE `sample_details` DROP INDEX `sample_details_crm_company_section_id_foreign`');
            } catch (\Throwable) {
                // Ignore if index already dropped / name differs.
            }
        }

        if (Schema::hasColumn('sample_details', 'crm_company_section_id') && ! Schema::hasColumn('sample_details', 'section_details')) {
            DB::statement('ALTER TABLE `sample_details` CHANGE COLUMN `crm_company_section_id` `section_details` TEXT NULL');
        }

        // Update custom field mappings so anything pointing at the old FK column now writes to section_details.
        if (Schema::hasColumn('custom_fields', 'sample_detail_column')) {
            DB::table('custom_fields')
                ->where('sample_detail_column', 'crm_company_section_id')
                ->update(['sample_detail_column' => 'section_details']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Best-effort rollback (data may not be recoverable once you repurpose FK ids).
        if (Schema::hasColumn('sample_details', 'section_details') && ! Schema::hasColumn('sample_details', 'crm_company_section_id')) {
            DB::statement('ALTER TABLE `sample_details` CHANGE COLUMN `section_details` `crm_company_section_id` BIGINT UNSIGNED NULL');
        }

        Schema::table('sample_details', function (Blueprint $table) {
            if (Schema::hasColumn('sample_details', 'crm_company_section_id')) {
                try {
                    $table->foreign('crm_company_section_id')->references('id')->on('crm_company_sections')->onDelete('set null');
                } catch (\Throwable) {
                    // Ignore if FK already exists / cannot be added.
                }
            }
        });
    }
};

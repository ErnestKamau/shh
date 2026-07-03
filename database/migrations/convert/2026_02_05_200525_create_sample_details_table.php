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
        if (Schema::hasTable('sample_details')) {
            return;
        }
        Schema::create('sample_details', function (Blueprint $table) {
            $table->uuid('id');
            $table->boolean('has_no_result_capture')->default(false);
            $table->string('sample_code')->index('idx_sample_details_sample_code_f41cb5c7');
            $table->uuid('analysis_type_id')->index('idx_sample_details_analysis_type_id_9893379f');
            $table->uuid('sample_condition_id')->nullable()->index('idx_sample_details_sample_condition_id_9072edab');
            $table->text('barcode')->nullable();
            $table->text('comments')->nullable();
            $table->text('gps')->nullable();
            $table->string('photo_url', 512)->nullable();
            $table->timestamps();
            $table->uuid('sample_header_id')->index('idx_sample_details_sample_header_id_7eca43d8');
            $table->uuid('sample_point_id')->nullable()->index('idx_sample_details_sample_point_id_a6f05bca');
            $table->text('section_details')->nullable();
            $table->uuid('crm_unit_id')->nullable();
            $table->uuid('company_product_id')->nullable()->index('idx_sample_details_company_product_id_9038b5fa');
            $table->text('main_body')->nullable();
            $table->text('header_body')->nullable();
            $table->boolean('is_ammendment')->default(false);
            $table->integer('ammendment_number')->nullable()->default(1);
            $table->uuid('main_standard')->nullable();
            $table->uuid('secondary_standard')->nullable();
            $table->string('short_code', 500)->nullable();
            $table->string('material_status', 500)->nullable();
            $table->uuid('third_standard_id')->nullable();
            $table->string('sample_no', 100)->nullable()->index('idx_sample_details_sample_no_354018f9');
            $table->string('no_of_samples', 100)->nullable();
            $table->string('no_of_pots_plants', 100)->nullable();
            $table->string('standard_tests', 700)->nullable();
            $table->text('compartiment_lot')->nullable();
            $table->longText('results')->nullable();
            $table->text('lab_sub_no')->nullable();
            $table->uuid('store_id')->nullable();
            $table->uuid('store_slot_id')->nullable();
            $table->text('quantity')->nullable();
            $table->uuid('reporting_unit_id')->nullable()->index('idx_sample_details_reporting_unit_id_4fd815d1');
            $table->date('mfg_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('batch_lot_no')->nullable();
            $table->integer('coa_number_target')->nullable();
            $table->text('coa_number')->nullable();
            $table->integer('comment_scope_type')->nullable()->default(0);
            $table->uuid('lab_id')->nullable()->index('idx_sample_details_lab_id_ae912439');
            $table->string('disposal_date', 50)->nullable();
            $table->text('sample_report_code')->nullable()->fulltext('ftx_sample_details_sample_report_code_9e7040d5');
            $table->text('notes_body')->nullable();
            $table->tinyInteger('is_disposed')->nullable()->default(0);
            $table->text('report_number')->nullable();
            $table->integer('company_sub_unit_id')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_details');
    }
};

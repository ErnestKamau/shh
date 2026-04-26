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
        Schema::create('sample_details', function (Blueprint $table) {
            $table->uuid('id');
            $table->boolean('has_no_result_capture')->default(false);
            $table->string('sample_code')->index('sample_code');
            $table->uuid('analysis_type_id')->index('analysis_type_id');
            $table->uuid('sample_condition_id')->nullable()->index('sample_condition_id');
            $table->text('barcode')->nullable();
            $table->text('comments')->nullable();
            $table->text('gps')->nullable();
            $table->string('photo_url', 512)->nullable();
            $table->timestamps();
            $table->uuid('sample_header_id')->index('sample_header_id');
            $table->uuid('sample_point_id')->nullable()->index('sample_point_id');
            $table->text('section_details')->nullable();
            $table->uuid('crm_unit_id')->nullable();
            $table->uuid('company_product_id')->nullable()->index('company_product_id');
            $table->text('main_body')->nullable();
            $table->text('header_body')->nullable();
            $table->boolean('is_ammendment')->default(false);
            $table->integer('ammendment_number')->nullable()->default(1);
            $table->uuid('main_standard')->nullable();
            $table->uuid('secondary_standard')->nullable();
            $table->string('short_code', 500)->nullable();
            $table->string('material_status', 500)->nullable();
            $table->uuid('third_standard_id')->nullable();
            $table->string('sample_no', 100)->nullable()->index('sample_no');
            $table->string('no_of_samples', 100)->nullable();
            $table->string('no_of_pots_plants', 100)->nullable();
            $table->string('standard_tests', 700)->nullable();
            $table->text('compartiment_lot')->nullable();
            $table->longText('results')->nullable();
            $table->text('lab_sub_no')->nullable();
            $table->uuid('store_id')->nullable();
            $table->uuid('store_slot_id')->nullable();
            $table->text('quantity')->nullable();
            $table->uuid('reporting_unit_id')->nullable()->index('idx_sample_details_reporting_unit_id_63d4a2d3');
            $table->date('mfg_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('batch_lot_no')->nullable();
            $table->integer('coa_number_target')->nullable();
            $table->text('coa_number')->nullable();
            $table->integer('comment_scope_type')->nullable()->default(0);
            $table->uuid('lab_id')->nullable()->index('lab_id');
            $table->string('disposal_date', 50)->nullable();
            $table->text('sample_report_code')->nullable()->fulltext('sample_report_code');
            $table->text('notes_body')->nullable();
            $table->tinyInteger('is_disposed')->nullable()->default(0);
            $table->text('report_number')->nullable();
            $table->integer('company_sub_unit_id')->nullable();
            $table->foreign(['analysis_type_id'], 'fk_sample_details_analysis_type_id_4bbdb281')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['company_product_id'], 'fk_sample_details_company_product_id_6a2b2201')->references(['id'])->on('company_products')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['lab_id'], 'fk_sample_details_lab_id_6032c7b2')->references(['id'])->on('labs')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['reporting_unit_id'], 'fk_sample_details_reporting_unit_id_6445cee8')->references(['id'])->on('reporting_units')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_condition_id'], 'fk_sample_details_sample_condition_id_297b33e9')->references(['id'])->on('sample_conditions')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_header_id'], 'fk_sample_details_sample_header_id_75a450ac')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_point_id'], 'fk_sample_details_sample_point_id_3d478b93')->references(['id'])->on('sample_points')->onUpdate('no action')->onDelete('set null');
            $table->foreign('crm_unit_id')->references('id')->on('crm_company_units')->onDelete('set null');
            $table->foreign('main_standard')->references('id')->on('standards')->onDelete('set null');
            $table->foreign('secondary_standard')->references('id')->on('standards')->onDelete('set null');
            $table->foreign('third_standard_id')->references('id')->on('standards')->onDelete('set null');
            $table->foreign('store_id')->references('id')->on('inventory_stores')->onDelete('set null');
            $table->foreign('store_slot_id')->references('id')->on('inventory_store_slots')->onDelete('set null');







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

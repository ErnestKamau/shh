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
        Schema::create('sample_points', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('crm_company_unit_id')->index('idx_sample_points_crm_company_unit_id_d3da4405');
            $table->timestamps();
            $table->smallInteger('active')->nullable();
            $table->string('gps', 512);
            $table->uuid('sample_point_area_id')->nullable()->index('sample_points_sample_point_area_id_foreign');
            $table->uuid('crm_area_id')->nullable()->index('idx_sample_points_crm_area_id_5dad28e2');
            $table->uuid('crm_sample_point_id')->nullable()->index('idx_sample_points_crm_sample_point_id_94d77acb');
            $table->uuid('crm_company_sub_unit_id')->nullable()->index('idx_sample_points_crm_company_sub_unit_id_9e16acae');
            $table->uuid('crm_customer_id')->nullable()->index('idx_sample_points_crm_customer_id_be2baf3e');
            $table->foreign(['sample_point_area_id'], 'fk_sample_points_sample_point_area_id_288fec45')->references(['id'])->on('sample_point_area')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['crm_area_id'], 'fk_sample_points_crm_area_id_49c57678')->references(['id'])->on('crm_areas')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['crm_company_sub_unit_id'], 'fk_sample_points_crm_company_sub_unit_id_f42a1dd4')->references(['id'])->on('crm_company_sub_units')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['crm_company_unit_id'], 'fk_sample_points_crm_company_unit_id_acbfb0fc')->references(['id'])->on('crm_company_units')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['crm_customer_id'], 'fk_sample_points_crm_customer_id_d6d2e4fc')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['crm_sample_point_id'], 'fk_sample_points_crm_sample_point_id_9dc4db32')->references(['id'])->on('crm_sample_points')->onUpdate('no action')->onDelete('set null');





            $table->primary(['id']);





        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_points');
    }
};

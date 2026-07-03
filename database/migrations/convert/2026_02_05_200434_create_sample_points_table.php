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
            $table->uuid('crm_company_unit_id')->index('idx_sample_points_crm_company_unit_id_c2640df9');
            $table->timestamps();
            $table->smallInteger('active')->nullable();
            $table->string('gps', 512);
            $table->uuid('sample_point_area_id')->nullable()->index('idx_sample_points_sample_point_area_id_a204ff19');
            $table->uuid('crm_area_id')->nullable()->index('idx_sample_points_crm_area_id_05b1a350');
            $table->uuid('crm_sample_point_id')->nullable()->index('idx_sample_points_crm_sample_point_id_9c992b4e');
            $table->uuid('crm_company_sub_unit_id')->nullable()->index('idx_sample_points_crm_company_sub_unit_id_4c424cbd');
            $table->uuid('crm_customer_id')->nullable()->index('idx_sample_points_crm_customer_id_a4297c20');

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

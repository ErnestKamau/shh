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
        Schema::table('sample_points', function (Blueprint $table) {
            $table->bigInteger('crm_area_id')->nullable()->after('sample_point_area_id');
            $table->bigInteger('crm_sample_point_id')->nullable()->after('crm_area_id');
            $table->bigInteger('crm_company_sub_unit_id')->nullable()->after('crm_sample_point_id');
            $table->bigInteger('crm_customer_id')->nullable()->after('crm_company_sub_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_points', function (Blueprint $table) {
            $table->dropColumn(['crm_area_id', 'crm_sample_point_id', 'crm_company_sub_unit_id', 'crm_customer_id']);
        });
    }
};

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
        Schema::table('sample_details', function (Blueprint $table) {
            $table->unsignedBigInteger('company_sub_unit_id')->nullable()->after('sample_point_id');
            $table->foreign('company_sub_unit_id')->references('id')->on('crm_company_sub_units')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_details', function (Blueprint $table) {
            $table->dropForeign(['company_sub_unit_id']);
            $table->dropColumn('company_sub_unit_id');
        });
    }
};

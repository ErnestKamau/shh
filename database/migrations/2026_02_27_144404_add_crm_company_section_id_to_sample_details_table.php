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
            $table->unsignedBigInteger('crm_company_section_id')->nullable()->after('sample_point_id');
            $table->foreign('crm_company_section_id')->references('id')->on('crm_company_sections')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('sample_details', function (Blueprint $table) {
            $table->dropForeign(['crm_company_section_id']);
            $table->dropColumn('crm_company_section_id');
        });
    }
};

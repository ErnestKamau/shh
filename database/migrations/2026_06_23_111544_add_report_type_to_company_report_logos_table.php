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
        Schema::table('company_report_logos', function (Blueprint $table) {
            $table->string('report_type')->nullable()->after('show_on_every_page');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_report_logos', function (Blueprint $table) {
            $table->dropColumn('report_type');
        });
    }
};

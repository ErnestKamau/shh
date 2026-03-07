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
        Schema::table('report_formats', function (Blueprint $table) {
            $table->string('results_display_type')->default('grid')->after('report_code'); // Options: grid, attachment_summary, hygiene_swabs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_formats', function (Blueprint $table) {
            $table->dropColumn('results_display_type');
        });
    }
};

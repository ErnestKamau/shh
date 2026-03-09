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
        Schema::table('report_format_sections', function (Blueprint $table) {
            $table->json('settings')->nullable()->after('custom_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_format_sections', function (Blueprint $table) {
            $table->dropColumn('settings');
        });
    }
};

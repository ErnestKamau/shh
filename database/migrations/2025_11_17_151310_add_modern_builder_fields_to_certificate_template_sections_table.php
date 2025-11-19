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
        Schema::table('certificate_template_sections', function (Blueprint $table) {
            $table->json('layout_structure')->nullable()->after('styling_options'); // rows/columns/cells structure
            $table->json('css_config')->nullable()->after('layout_structure'); // section-level CSS configuration
            $table->json('data_config')->nullable()->after('css_config'); // section-level data configuration
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_template_sections', function (Blueprint $table) {
            $table->dropColumn(['layout_structure', 'css_config', 'data_config']);
        });
    }
};

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
        Schema::table('certificate_template_elements', function (Blueprint $table) {
            $table->json('css_config')->nullable()->after('styling'); // CSS configuration (margin, padding, width, height, alignment, border, background, text color, font, custom class, custom CSS)
            $table->json('data_config')->nullable()->after('css_config'); // data configuration (static/dynamic_model/dynamic_derived)
            $table->string('parent_cell_id')->nullable()->after('data_config'); // references cell ID in section's layout_structure
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_template_elements', function (Blueprint $table) {
            $table->dropColumn(['css_config', 'data_config', 'parent_cell_id']);
        });
    }
};

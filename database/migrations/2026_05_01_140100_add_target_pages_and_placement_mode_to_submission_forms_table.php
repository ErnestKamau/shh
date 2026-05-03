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
        Schema::table('submission_forms', function (Blueprint $table) {
            $table->json('target_pages')->nullable()->after('print_template_name');
            $table->string('placement_mode', 50)->default('button_trigger')->after('target_pages');
            $table->index('placement_mode', 'submission_forms_placement_mode_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_forms', function (Blueprint $table) {
            $table->dropIndex('submission_forms_placement_mode_index');
            $table->dropColumn(['target_pages', 'placement_mode']);
        });
    }
};

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
        Schema::table('submission_form_sections', function (Blueprint $table) {
            $table->string('section_alignment', 20)->default('left')->after('section_type');
            $table->json('section_logos')->nullable()->after('section_alignment');
            $table->index('section_alignment', 'submission_form_sections_alignment_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_form_sections', function (Blueprint $table) {
            $table->dropIndex('submission_form_sections_alignment_idx');
            $table->dropColumn(['section_alignment', 'section_logos']);
        });
    }
};

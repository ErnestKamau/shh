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
            if (! Schema::hasColumn('submission_form_sections', 'is_hidden')) {
                $table->boolean('is_hidden')->default(false)->after('sort_order');
            }
        });

        Schema::table('submission_form_elements', function (Blueprint $table) {
            if (! Schema::hasColumn('submission_form_elements', 'is_hidden')) {
                $table->boolean('is_hidden')->default(false)->after('is_readonly');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_form_sections', function (Blueprint $table) {
            if (Schema::hasColumn('submission_form_sections', 'is_hidden')) {
                $table->dropColumn('is_hidden');
            }
        });

        Schema::table('submission_form_elements', function (Blueprint $table) {
            if (Schema::hasColumn('submission_form_elements', 'is_hidden')) {
                $table->dropColumn('is_hidden');
            }
        });
    }
};

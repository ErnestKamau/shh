<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add specific_feedback column to customerfeedbacks if missing
     * (e.g. when add_specific_feedback migration was not run).
     */
    public function up(): void
    {
        Schema::table('customerfeedbacks', function (Blueprint $table) {
            if (! Schema::hasColumn('customerfeedbacks', 'specific_feedback')) {
                $table->text('specific_feedback')->nullable()->after('has_issues');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customerfeedbacks', function (Blueprint $table) {
            if (Schema::hasColumn('customerfeedbacks', 'specific_feedback')) {
                $table->dropColumn('specific_feedback');
            }
        });
    }
};

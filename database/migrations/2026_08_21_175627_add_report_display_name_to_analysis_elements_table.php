<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('analysis_elements')) {
            return;
        }

        Schema::table('analysis_elements', function (Blueprint $table): void {
            if (! Schema::hasColumn('analysis_elements', 'report_display_name')) {
                $table->string('report_display_name')->nullable()->after('analyte_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('analysis_elements')) {
            return;
        }

        Schema::table('analysis_elements', function (Blueprint $table): void {
            if (Schema::hasColumn('analysis_elements', 'report_display_name')) {
                $table->dropColumn('report_display_name');
            }
        });
    }
};

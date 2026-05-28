<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('analysis_types') || Schema::hasColumn('analysis_types', 'hybrid_worksheet_id')) {
            return;
        }

        Schema::table('analysis_types', function (Blueprint $table) {
            $table->uuid('hybrid_worksheet_id')->nullable()->after('grouped_worksheet_holder_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('analysis_types') || ! Schema::hasColumn('analysis_types', 'hybrid_worksheet_id')) {
            return;
        }

        Schema::table('analysis_types', function (Blueprint $table) {
            $table->dropColumn('hybrid_worksheet_id');
        });
    }
};

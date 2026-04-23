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
        Schema::table('qc_results', function (Blueprint $table) {
            if (!Schema::hasColumn('qc_results', 'is_qc_processed')) {
                $table->boolean('is_qc_processed')->default(false)->after('status_code');
            }
            if (!Schema::hasColumn('qc_results', 'analyte_processed_id')) {
                $table->unsignedBigInteger('analyte_processed_id')->nullable()->after('is_qc_processed');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qc_results', function (Blueprint $table) {
            $table->dropColumn(['is_qc_processed', 'analyte_processed_id']);
        });
    }
};

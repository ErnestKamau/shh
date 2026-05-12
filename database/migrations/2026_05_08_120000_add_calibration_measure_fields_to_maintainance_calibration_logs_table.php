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
        Schema::table('maintainance_calibration_logs', function (Blueprint $table) {
            $table->decimal('correction_factor', 12, 6)->nullable()->after('reference_number');
            $table->decimal('uncertainty_of_measure', 12, 6)->nullable()->after('correction_factor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintainance_calibration_logs', function (Blueprint $table) {
            $table->dropColumn(['correction_factor', 'uncertainty_of_measure']);
        });
    }
};

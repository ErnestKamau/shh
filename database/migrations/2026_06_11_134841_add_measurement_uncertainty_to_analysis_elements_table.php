<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analysis_elements', function (Blueprint $table) {
            $table->decimal('measurement_uncertainty', 12, 4)->nullable()->after('lod');
        });
    }

    public function down(): void
    {
        Schema::table('analysis_elements', function (Blueprint $table) {
            $table->dropColumn('measurement_uncertainty');
        });
    }
};

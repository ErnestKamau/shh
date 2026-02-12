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
        Schema::table('analysis_types', function (Blueprint $table) {
            $table->unsignedBigInteger('procedure_worksheet_id')->nullable()->after('has_no_result');
        });

        Schema::table('analysis_elements', function (Blueprint $table) {
            $table->unsignedBigInteger('procedure_worksheet_id')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analysis_types', function (Blueprint $table) {
            $table->dropColumn('procedure_worksheet_id');
        });

        Schema::table('analysis_elements', function (Blueprint $table) {
            $table->dropColumn('procedure_worksheet_id');
        });
    }
};

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
        Schema::table('captured_results', function (Blueprint $table) {
            $table->bigInteger('analysis_element_id')->nullable()->after('stage_header_id');
            $table->bigInteger('formular_id')->nullable()->after('analysis_element_id');
            $table->bigInteger('method_sequence_id')->nullable()->after('formular_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('captured_results', function (Blueprint $table) {
            $table->dropColumn(['analysis_element_id', 'formular_id', 'method_sequence_id']);
        });
    }
};

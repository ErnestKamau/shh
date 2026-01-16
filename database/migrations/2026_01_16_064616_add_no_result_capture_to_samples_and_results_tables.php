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
        Schema::table('sample_details', function (Blueprint $table) {
            $table->boolean('has_no_result_capture')->default(false)->after('id');
        });

        Schema::table('captured_results', function (Blueprint $table) {
            $table->boolean('has_no_result_capture')->default(false)->after('id');
        });

        Schema::table('results', function (Blueprint $table) {
            $table->boolean('has_no_result_capture')->default(false)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_details', function (Blueprint $table) {
            $table->dropColumn('has_no_result_capture');
        });

        Schema::table('captured_results', function (Blueprint $table) {
            $table->dropColumn('has_no_result_capture');
        });

        Schema::table('results', function (Blueprint $table) {
            $table->dropColumn('has_no_result_capture');
        });
    }
};

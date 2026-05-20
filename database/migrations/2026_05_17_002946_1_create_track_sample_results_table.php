<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('track_sample_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('track_id');
            $table->uuid('captured_result_id');
            $table->string('sample_code');
            $table->string('parameter');
            $table->string('method')->nullable();
            $table->string('reporting_unit')->nullable();
            $table->text('result')->nullable();
            $table->string('standard_limit')->nullable();
            $table->text('remark')->nullable();
            $table->boolean('remark_is_auto_calculated')->default(false);
            $table->string('reporting_symbol', 50)->nullable();
            $table->text('raw_numeric_result')->nullable();
            $table->uuid('analyst_id')->nullable();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();

            $table->foreign('track_id')->references('id')->on('sample_captured_test_stages_track')->cascadeOnDelete();
            $table->foreign('captured_result_id')->references('id')->on('captured_results')->cascadeOnDelete();
            $table->foreign('analyst_id')->references('id')->on('users')->nullOnDelete();

            $table->index('track_id');
            $table->index('captured_result_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('track_sample_results');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_captured_test_stages_track', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('stage_header_run_id');
            $table->uuid('captured_result_id');
            $table->uuid('sample_detail_id');
            $table->uuid('stage_header_id');
            $table->uuid('test_stage_id');
            $table->uuid('user_id')->nullable();
            $table->boolean('auto_started')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expected_end_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->uuid('ended_by')->nullable();
            $table->timestamp('overtime_flagged_at')->nullable();
            $table->text('auto_notes')->nullable();
            $table->string('status')->default('pending');
            $table->json('equipment_data')->nullable();
            $table->json('media_data')->nullable();
            $table->json('controls_data')->nullable();
            $table->text('result')->nullable();
            $table->json('diluents_data')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('reading_date')->nullable();
            $table->uuid('read_by')->nullable();
            $table->timestamp('results_posted_at')->nullable();
            $table->uuid('results_posted_by')->nullable();
            $table->timestamps();

            $table->foreign('stage_header_run_id')->references('id')->on('stage_header_runs')->cascadeOnDelete();
            $table->foreign('captured_result_id')->references('id')->on('captured_results')->cascadeOnDelete();
            $table->foreign('sample_detail_id')->references('id')->on('sample_details')->cascadeOnDelete();
            $table->foreign('stage_header_id')->references('id')->on('stage_headers')->cascadeOnDelete();
            $table->foreign('test_stage_id')->references('id')->on('test_stages')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('ended_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('read_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('results_posted_by')->references('id')->on('users')->nullOnDelete();

            $table->index('stage_header_run_id');
            $table->index('test_stage_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_captured_test_stages_track');
    }
};

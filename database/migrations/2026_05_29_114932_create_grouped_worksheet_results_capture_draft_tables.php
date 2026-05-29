<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grouped_worksheet_results_capture_drafts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sample_header_id');
            $table->uuid('grouped_worksheet_holder_id');
            $table->uuid('captured_result_id');
            $table->text('result')->nullable();
            $table->string('reporting_symbol', 16)->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->unique(
                ['sample_header_id', 'grouped_worksheet_holder_id', 'captured_result_id'],
                'gw_results_capture_drafts_unique'
            );
            $table->index(['sample_header_id', 'grouped_worksheet_holder_id'], 'gw_results_capture_drafts_batch_idx');
        });

        Schema::create('grouped_worksheet_results_capture_sample_drafts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sample_header_id');
            $table->uuid('grouped_worksheet_holder_id');
            $table->uuid('sample_detail_id');
            $table->text('header_body')->nullable();
            $table->text('main_body')->nullable();
            $table->text('notes_body')->nullable();
            $table->timestamps();

            $table->unique(
                ['sample_header_id', 'grouped_worksheet_holder_id', 'sample_detail_id'],
                'gw_results_capture_sample_drafts_unique'
            );
            $table->index(['sample_header_id', 'grouped_worksheet_holder_id'], 'gw_results_capture_sample_drafts_batch_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grouped_worksheet_results_capture_sample_drafts');
        Schema::dropIfExists('grouped_worksheet_results_capture_drafts');
    }
};

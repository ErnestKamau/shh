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
        if (Schema::hasTable('report_format_sample_analysis_stage')) {
            return;
        }
        Schema::create('report_format_sample_analysis_stage', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('sample_analysis_stage_id');
            $table->uuid('report_format_id')->index('idx_report_format_sample_analysis_stage_report_format_6dc4e643');
            $table->string('document_code')->nullable();
            $table->date('issue_date')->nullable();
            $table->string('revision_number')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['sample_analysis_stage_id', 'report_format_id'], 'rf_sas_unique');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_format_sample_analysis_stage');
    }
};

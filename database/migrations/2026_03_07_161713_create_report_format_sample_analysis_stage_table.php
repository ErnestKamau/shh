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
        Schema::create('report_format_sample_analysis_stage', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sample_analysis_stage_id');
            $table->bigInteger('report_format_id');
            $table->string('document_code')->nullable();
            $table->date('issue_date')->nullable();
            $table->string('revision_number')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('sample_analysis_stage_id', 'rf_sas_stage_id_fk')
                ->references('id')->on('sample_analysis_stages')->onDelete('cascade');
            $table->foreign('report_format_id', 'rf_sas_format_id_fk')
                ->references('id')->on('report_formats')->onDelete('cascade');

            // A report format can only be attached to a lab section once
            $table->unique(['sample_analysis_stage_id', 'report_format_id'], 'rf_sas_unique');
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

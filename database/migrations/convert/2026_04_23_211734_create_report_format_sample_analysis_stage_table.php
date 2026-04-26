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
            $table->uuid('id');
            $table->uuid('sample_analysis_stage_id');
            $table->uuid('report_format_id')->index('rf_sas_format_id_fk');
            $table->string('document_code')->nullable();
            $table->date('issue_date')->nullable();
            $table->string('revision_number')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['sample_analysis_stage_id', 'report_format_id'], 'rf_sas_unique');
            $table->foreign(['report_format_id'], 'rf_sas_format_id_fk')->references(['id'])->on('report_formats')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_analysis_stage_id'], 'rf_sas_stage_id_fk')->references(['id'])->on('sample_analysis_stages')->onUpdate('no action')->onDelete('cascade');
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

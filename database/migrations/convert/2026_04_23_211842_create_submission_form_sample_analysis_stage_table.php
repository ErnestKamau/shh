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
        Schema::create('submission_form_sample_analysis_stage', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('submission_form_id')->index('submission_form_sample_analysis_stage_submission_form_id_foreign');
            $table->uuid('sample_analysis_stage_id')->index('idx_submission_form_sample_analysis_stage_sample_analy_12793b1e');
            $table->timestamps();
            $table->foreign(['submission_form_id'], 'fk_submission_form_sample_analysis_stage_submission_fo_ad11ba44')->references(['id'])->on('submission_forms')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_analysis_stage_id'], 'fk_submission_form_sample_analysis_stage_sample_analys_ae4d8763')->references(['id'])->on('sample_analysis_stages')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_form_sample_analysis_stage');
    }
};

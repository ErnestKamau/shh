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
            $table->uuid('submission_form_id')->index('idx_submission_form_sample_analysis_stage_submission_f_48cf5b6d');
            $table->uuid('sample_analysis_stage_id')->index('idx_submission_form_sample_analysis_stage_sample_analy_010080b8');
            $table->timestamps();

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

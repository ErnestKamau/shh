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
            $table->id();
            $table->bigInteger('submission_form_id');
            $table->bigInteger('sample_analysis_stage_id');
            
            $table->foreign('submission_form_id')->references('id')->on('submission_forms')->onDelete('cascade');
            $table->foreign('sample_analysis_stage_id')->references('id')->on('sample_analysis_stages')->onDelete('cascade');
            
            $table->timestamps();
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

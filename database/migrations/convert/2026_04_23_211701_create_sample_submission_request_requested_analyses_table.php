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
        Schema::create('sample_submission_request_requested_analyses', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('sample_submission_request_id')->index('idx_sample_submission_request_requested_analyses_sampl_8bf1bfeb');
            $table->string('analysis_key')->nullable()->index('idx_sample_submission_request_requested_analyses_analy_019d857e');
            $table->string('analysis_label')->nullable();
            $table->timestamps();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_submission_request_requested_analyses');
    }
};

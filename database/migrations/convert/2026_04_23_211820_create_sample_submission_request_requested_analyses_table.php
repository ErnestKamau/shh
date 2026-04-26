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
            $table->uuid('sample_submission_request_id')->index('ssra_req_id_idx');
            $table->string('analysis_key')->nullable()->index('ssra_analysis_key_idx');
            $table->string('analysis_label')->nullable();
            $table->timestamps();
            $table->foreign(['sample_submission_request_id'], 'fk_sample_submission_request_requested_analyses_sample_b0b24d94')->references(['id'])->on('sample_submission_requests')->onUpdate('no action')->onDelete('cascade');

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

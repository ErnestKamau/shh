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
        if (! Schema::hasTable('sample_submission_request_requested_analyses')) {
            Schema::create('sample_submission_request_requested_analyses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sample_submission_request_id');
                $table->string('analysis_key')->nullable();
                $table->string('analysis_label')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('sample_submission_request_requested_analyses', function (Blueprint $table) {
            $table->index('sample_submission_request_id', 'ssra_req_id_idx');
            $table->index('analysis_key', 'ssra_analysis_key_idx');
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

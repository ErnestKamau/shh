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
        Schema::create('sample_submission_request_suspects', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('sample_submission_request_id')->index('ssrs_req_id_idx');
            $table->unsignedInteger('serial_number')->nullable()->index('ssrs_serial_no_idx');
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('sex')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->string('id_passport_number')->nullable();
            $table->timestamps();
            $table->foreign(['sample_submission_request_id'], 'fk_sample_submission_request_suspects_sample_submissio_1bdd5f9f')->references(['id'])->on('sample_submission_requests')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_submission_request_suspects');
    }
};

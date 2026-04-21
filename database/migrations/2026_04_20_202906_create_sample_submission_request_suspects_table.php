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
        if (! Schema::hasTable('sample_submission_request_suspects')) {
            Schema::create('sample_submission_request_suspects', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sample_submission_request_id');
                $table->unsignedInteger('serial_number')->nullable();
                $table->string('first_name')->nullable();
                $table->string('middle_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('sex')->nullable();
                $table->date('date_of_birth')->nullable();
                $table->string('nationality')->nullable();
                $table->string('id_passport_number')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('sample_submission_request_suspects', function (Blueprint $table) {
            $table->index('sample_submission_request_id', 'ssrs_req_id_idx');
            $table->index('serial_number', 'ssrs_serial_no_idx');
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

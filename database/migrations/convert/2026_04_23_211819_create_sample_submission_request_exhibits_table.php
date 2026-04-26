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
        Schema::create('sample_submission_request_exhibits', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('sample_submission_request_id')->index('ssre_req_id_idx');
            $table->uuid('sample_detail_id')->nullable()->index('ssre_sample_detail_id_idx');
            $table->unsignedInteger('serial_number')->nullable()->index('ssre_serial_no_idx');
            $table->unsignedInteger('number_of_items')->nullable();
            $table->text('item_description')->nullable();
            $table->string('suspected_item')->nullable();
            $table->timestamps();
            $table->foreign(['sample_detail_id'], 'fk_sample_submission_request_exhibits_sample_detail_id_11a32e48')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_submission_request_id'], 'fk_sample_submission_request_exhibits_sample_submissio_186edc74')->references(['id'])->on('sample_submission_requests')->onUpdate('no action')->onDelete('cascade');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_submission_request_exhibits');
    }
};

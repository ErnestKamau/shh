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
            $table->uuid('sample_submission_request_id')->index('idx_sample_submission_request_exhibits_sample_submissi_52633f23');
            $table->uuid('sample_detail_id')->nullable()->index('idx_sample_submission_request_exhibits_sample_detail_i_9c4e4171');
            $table->unsignedInteger('serial_number')->nullable()->index('idx_sample_submission_request_exhibits_serial_number_94e4d6dc');
            $table->unsignedInteger('number_of_items')->nullable();
            $table->text('item_description')->nullable();
            $table->string('suspected_item')->nullable();
            $table->timestamps();

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

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
        if (! Schema::hasTable('sample_submission_request_exhibits')) {
            Schema::create('sample_submission_request_exhibits', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sample_submission_request_id');
                $table->unsignedBigInteger('sample_detail_id')->nullable();
                $table->unsignedInteger('serial_number')->nullable();
                $table->unsignedInteger('number_of_items')->nullable();
                $table->text('item_description')->nullable();
                $table->string('suspected_item')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('sample_submission_request_exhibits', function (Blueprint $table) {
            $table->index('sample_submission_request_id', 'ssre_req_id_idx');
            $table->index('sample_detail_id', 'ssre_sample_detail_id_idx');
            $table->index('serial_number', 'ssre_serial_no_idx');
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

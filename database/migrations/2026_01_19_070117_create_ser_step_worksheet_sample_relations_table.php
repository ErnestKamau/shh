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
        Schema::create('ser_step_worksheet_sample_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ser_header_id')->index();
            $table->unsignedBigInteger('ser_worksheet_step_id')->nullable();
            $table->integer('step_number')->default(0);
            $table->unsignedInteger('measurand_id')->nullable();
            $table->unsignedInteger('equipment_id')->nullable();
            $table->unsignedBigInteger('analyst_id')->nullable(); // Single analyst for specific step
            $table->string('result_value')->nullable(); // To store any specific result for the step if needed
            $table->timestamps();

            $table->foreign('ser_header_id', 'fk_stp_hdr_id')->references('id')->on('ser_header_worksheet_sample_relations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ser_step_worksheet_sample_relations');
    }
};

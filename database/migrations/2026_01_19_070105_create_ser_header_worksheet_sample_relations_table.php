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
        Schema::create('ser_header_worksheet_sample_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sample_detail_id')->index();
            $table->unsignedInteger('analysis_type_id')->nullable()->index();
            $table->string('dilution_used')->nullable();
            $table->date('date_received')->nullable();
            $table->date('date_tested')->nullable();
            $table->string('room_temperature')->nullable();
            $table->time('start_time')->nullable();
            $table->unsignedInteger('method_id')->nullable();
            $table->json('analyst_ids')->nullable(); // Store multiple analyst IDs
            $table->timestamps();

             // Foreign keys (assuming standard table names, adjusting if necessary based on user context)
             // Using integer for compatibility with potential existing non-bigint IDs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ser_header_worksheet_sample_relations');
    }
};

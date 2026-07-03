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
        if (Schema::hasTable('ser_header_worksheet_sample_relations')) {
            return;
        }
        Schema::create('ser_header_worksheet_sample_relations', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('sample_detail_id')->index('idx_ser_header_worksheet_sample_relations_sample_detai_df48a985');
            $table->uuid('analysis_type_id')->nullable()->index('idx_ser_header_worksheet_sample_relations_analysis_typ_7b6b74ac');
            $table->string('dilution_used')->nullable();
            $table->date('date_received')->nullable();
            $table->date('date_tested')->nullable();
            $table->string('room_temperature')->nullable();
            $table->time('start_time')->nullable();
            $table->unsignedInteger('method_id')->nullable();
            $table->longText('analyst_ids')->nullable();
            $table->timestamps();

            $table->primary(['id']);

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

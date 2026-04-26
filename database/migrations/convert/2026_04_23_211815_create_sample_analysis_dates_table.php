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
        Schema::create('sample_analysis_dates', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('sample_detail_id')->index('idx_sample_analysis_dates_sample_detail_id_f0b1f111');
            $table->uuid('sample_header_id')->index('idx_sample_analysis_dates_sample_header_id_993fc3e8');
            $table->text('analysis_dates');
            $table->date('start_analysis_date');
            $table->foreign(['sample_detail_id'], 'fk_sample_analysis_dates_sample_detail_id_5367e6fe')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_sample_analysis_dates_sample_header_id_c88bb7fc')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_analysis_dates');
    }
};

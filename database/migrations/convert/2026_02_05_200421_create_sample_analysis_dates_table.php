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
        if (Schema::hasTable('sample_analysis_dates')) {
            return;
        }
        Schema::create('sample_analysis_dates', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('sample_detail_id')->index('idx_sample_analysis_dates_sample_detail_id_86bf3c29');
            $table->uuid('sample_header_id')->index('idx_sample_analysis_dates_sample_header_id_1fc5533d');
            $table->text('analysis_dates');
            $table->date('start_analysis_date');

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

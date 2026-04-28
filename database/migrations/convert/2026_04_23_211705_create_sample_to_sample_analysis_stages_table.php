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
        Schema::create('sample_to_sample_analysis_stages', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('sample_type_id')->index('idx_sample_to_sample_analysis_stages_sample_type_id_cf99ad41');
            $table->uuid('sample_analysis_stage_id')->index('idx_sample_to_sample_analysis_stages_sample_analysis_s_7607e3c4');
            $table->timestamps();
            $table->smallInteger('active')->nullable()->default(0);

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_to_sample_analysis_stages');
    }
};

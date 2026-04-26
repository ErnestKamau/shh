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
            $table->uuid('sample_type_id')->index('idx_sample_to_sample_analysis_stages_sample_type_id_31ff1250');
            $table->uuid('sample_analysis_stage_id')->index('idx_sample_to_sample_analysis_stages_sample_analysis_s_c41f06e6');
            $table->timestamps();
            $table->smallInteger('active')->nullable()->default(0);
            $table->foreign(['sample_analysis_stage_id'], 'fk_sample_to_sample_analysis_stages_sample_analysis_st_4df8a901')->references(['id'])->on('sample_analysis_stages')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_type_id'], 'fk_sample_to_sample_analysis_stages_sample_type_id_761dff80')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('cascade');


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

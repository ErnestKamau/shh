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
        if (Schema::hasTable('sample_analysis_type_relation')) {
            return;
        }
        Schema::create('sample_analysis_type_relation', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('batch_id')->index('idx_sample_analysis_type_relation_batch_id_8a2d0e76');
            $table->uuid('sample_detail_id')->index('idx_sample_analysis_type_relation_sample_detail_id_719b0ffb');
            $table->uuid('analysis_type_id')->index('idx_sample_analysis_type_relation_analysis_type_id_f6ac6279');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_analysis_type_relation');
    }
};

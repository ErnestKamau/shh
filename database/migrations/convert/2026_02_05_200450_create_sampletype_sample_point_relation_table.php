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
        if (Schema::hasTable('sampletype_sample_point_relation')) {
            return;
        }
        Schema::create('sampletype_sample_point_relation', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('sample_type_id');
            $table->uuid('sample_point_id')->index('idx_sampletype_sample_point_relation_sample_point_id_c49b29ea');
            $table->timestamps();

            $table->unique(['sample_type_id', 'sample_point_id'], 'unique_sample_type_sample_point');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sampletype_sample_point_relation');
    }
};

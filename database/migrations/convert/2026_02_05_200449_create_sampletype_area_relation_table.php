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
        if (Schema::hasTable('sampletype_area_relation')) {
            return;
        }
        Schema::create('sampletype_area_relation', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('sample_type_id');
            $table->uuid('area_id')->index('idx_sampletype_area_relation_area_id_eb0a2092');
            $table->timestamps();

            $table->unique(['sample_type_id', 'area_id'], 'unique_sample_type_area');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sampletype_area_relation');
    }
};

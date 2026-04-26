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
        Schema::create('sampletype_area_relation', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('sample_type_id');
            $table->uuid('area_id')->index('sampletype_area_relation_area_id_foreign');
            $table->timestamps();

            $table->unique(['sample_type_id', 'area_id'], 'unique_sample_type_area');
            $table->foreign(['area_id'], 'fk_sampletype_area_relation_area_id_8a8d5dd5')->references(['id'])->on('crm_areas')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_type_id'], 'fk_sampletype_area_relation_sample_type_id_f00d6d77')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('cascade');
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

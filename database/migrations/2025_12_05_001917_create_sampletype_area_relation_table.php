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
            $table->id();
            $table->bigInteger('sample_type_id');
            $table->unsignedBigInteger('area_id');
            $table->timestamps();
            
            $table->foreign('sample_type_id')->references('id')->on('sample_types')->onDelete('cascade');
            $table->foreign('area_id')->references('id')->on('crm_areas')->onDelete('cascade');
            $table->unique(['sample_type_id', 'area_id'], 'unique_sample_type_area');
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

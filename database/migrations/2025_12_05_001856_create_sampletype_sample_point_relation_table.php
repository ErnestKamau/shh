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
        Schema::create('sampletype_sample_point_relation', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sample_type_id');
            $table->unsignedBigInteger('sample_point_id');
            $table->timestamps();
            
            $table->foreign('sample_type_id')->references('id')->on('sample_types')->onDelete('cascade');
            $table->foreign('sample_point_id')->references('id')->on('crm_sample_points')->onDelete('cascade');
            $table->unique(['sample_type_id', 'sample_point_id'], 'unique_sample_type_sample_point');
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

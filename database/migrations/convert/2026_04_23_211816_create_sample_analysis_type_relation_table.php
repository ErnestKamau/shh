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
        Schema::create('sample_analysis_type_relation', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('batch_id');
            $table->uuid('sample_detail_id')->index('idx_sample_analysis_type_relation_sample_detail_id_ca0562fc');
            $table->uuid('analysis_type_id')->index('idx_sample_analysis_type_relation_analysis_type_id_125f7a0e');
            $table->foreign(['analysis_type_id'], 'fk_sample_analysis_type_relation_analysis_type_id_09a1a145')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_detail_id'], 'fk_sample_analysis_type_relation_sample_detail_id_31d9dff9')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('cascade');


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

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
            $table->uuid('id');
            $table->uuid('sample_type_id');
            $table->uuid('sample_point_id')->index('sampletype_sample_point_relation_sample_point_id_foreign');
            $table->timestamps();

            $table->unique(['sample_type_id', 'sample_point_id'], 'unique_sample_type_sample_point');
            $table->foreign(['sample_point_id'], 'fk_sampletype_sample_point_relation_sample_point_id_af6516ee')->references(['id'])->on('crm_sample_points')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_type_id'], 'fk_sampletype_sample_point_relation_sample_type_id_21358013')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('cascade');
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

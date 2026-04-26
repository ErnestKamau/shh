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
        Schema::create('analysis_guides', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('guide_name')->nullable();
            $table->uuid('analyte_id')->index('analyte_id');
            $table->uuid('analysis_type_id')->index('analysis_type_id');
            $table->double('value')->nullable();
            $table->string('comments', 1024)->nullable();
            $table->string('recommendations', 1024)->nullable();
            $table->timestamps();
            $table->uuid('standard_id')->nullable()->index('idx_analysis_guides_standard_id_6664c376');
            $table->string('standard_value_type')->nullable();
            $table->string('high')->nullable();
            $table->string('low')->nullable();
            $table->uuid('standard_value_id')->nullable()->index('idx_analysis_guides_standard_value_id_086b89cf');
            $table->string('standard_is_value')->nullable();
            $table->foreign(['analysis_type_id'], 'fk_analysis_guides_analysis_type_id_e15f1068')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['analyte_id'], 'fk_analysis_guides_analyte_id_cb7db970')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['standard_id'], 'fk_analysis_guides_standard_id_b2c82c24')->references(['id'])->on('standards')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['standard_value_id'], 'fk_analysis_guides_standard_value_id_a7efc6ec')->references(['id'])->on('standard_values')->onUpdate('no action')->onDelete('set null');




            $table->primary(['id']);




        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_guides');
    }
};

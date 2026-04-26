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
        Schema::create('qc_processed_result', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('sample_type_id')->index('idx_qc_processed_result_sample_type_id_4419816f');
            $table->uuid('analysis_type_id')->index('idx_qc_processed_result_analysis_type_id_1b508c03');
            $table->uuid('analyte_id')->index('idx_qc_processed_result_analyte_id_31dfd942');
            $table->integer('method_id');
            $table->uuid('standard_id')->index('idx_qc_processed_result_standard_id_46d594d7');
            $table->uuid('standard_value_id')->index('idx_qc_processed_result_standard_value_id_52d59258');
            $table->double('robust_standard_deviation')->nullable();
            $table->double('robust_mean')->nullable();
            $table->double('robust_median')->nullable();
            $table->double('robust_cv')->nullable();
            $table->double('robust_cv_percentage')->nullable();
            $table->foreign(['analysis_type_id'], 'fk_qc_processed_result_analysis_type_id_17936981')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['analyte_id'], 'fk_qc_processed_result_analyte_id_847ea0d1')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_type_id'], 'fk_qc_processed_result_sample_type_id_93101bbe')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['standard_id'], 'fk_qc_processed_result_standard_id_31b3bf15')->references(['id'])->on('standards')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['standard_value_id'], 'fk_qc_processed_result_standard_value_id_516e8a52')->references(['id'])->on('standard_values')->onUpdate('no action')->onDelete('cascade');





            $table->primary(['id']);





        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qc_processed_result');
    }
};

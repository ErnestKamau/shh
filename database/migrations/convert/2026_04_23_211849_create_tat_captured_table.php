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
        Schema::create('tat_captured', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('captured_result_id')->index('idx_tat_captured_captured_result_id_f8947ee3');
            $table->uuid('analysis_type_id')->index('idx_tat_captured_analysis_type_id_75a1f3b1');
            $table->uuid('analyte_id')->index('idx_tat_captured_analyte_id_8415b615');
            $table->uuid('sample_type_id')->index('idx_tat_captured_sample_type_id_3483a5e6');
            $table->uuid('sample_detail_id')->index('idx_tat_captured_sample_detail_id_48425844');
            $table->string('result')->nullable();
            $table->integer('analyst_id');
            $table->integer('tat_overdue_days')->default(0);
            $table->dateTime('tat_date');
            $table->dateTime('finished_date')->nullable();
            $table->boolean('is_complete')->default(false);
            $table->uuid('sample_header_id')->default(0)->index('idx_tat_captured_sample_header_id_b5ff0b5d');
            $table->integer('tat_remark')->nullable();
            $table->dateTime('start_date_analysis')->nullable();
            $table->foreign(['analysis_type_id'], 'fk_tat_captured_analysis_type_id_d2586f85')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['analyte_id'], 'fk_tat_captured_analyte_id_90fcad44')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['captured_result_id'], 'fk_tat_captured_captured_result_id_ce707f0f')->references(['id'])->on('captured_results')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_detail_id'], 'fk_tat_captured_sample_detail_id_429ce006')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_tat_captured_sample_header_id_923cf294')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_type_id'], 'fk_tat_captured_sample_type_id_3e3e34f0')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('cascade');






            $table->primary(['id']);






        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tat_captured');
    }
};

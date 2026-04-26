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
        Schema::create('quotation_details_analysis_type', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('quotation_detail_id')->index('idx_quotation_details_analysis_type_quotation_detail_i_36664694');
            $table->uuid('analysis_type_id')->index('idx_quotation_details_analysis_type_analysis_type_id_4bccddc2');
            $table->foreign(['analysis_type_id'], 'fk_quotation_details_analysis_type_analysis_type_id_abc46297')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['quotation_detail_id'], 'fk_quotation_details_analysis_type_quotation_detail_id_bb14845c')->references(['id'])->on('quotation_details')->onUpdate('no action')->onDelete('cascade');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_details_analysis_type');
    }
};

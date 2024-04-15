<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableQuotationDetailsAnalysisType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_details_analysis_type', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('quotation_header_id');
            $table->integer('analysis_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotation_details_analysis_type');
    }
}

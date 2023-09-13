<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSampleAnalyaisDatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sample_analysis_dates', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('sample_detail_id');
            $table->integer('sample_header_id');
            $table->text('analysis_dates');
            $table->date('start_analysis_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sample_analyais_dates');
    }
}

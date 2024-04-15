<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSampleAnalysisTypeRelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sample_analysis_type_relation', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('batch_id');
            $table->integer('sample_detail_id');
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
        Schema::dropIfExists('sample_analysis_type_relation');
    }
}

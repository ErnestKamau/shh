<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTatCapturedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tat_captured', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('captured_result_id');
            $table->integer('analysis_type_id');
            $table->integer('analyte_id');
            $table->integer('sample_type_id');
            $table->integer('sample_detail_id');
            $table->string('result')->nullable();
            $table->integer('analyst_id');
            $table->integer('tat_overdue_days')->default(0);
            $table->dateTime('tat_date');
            $table->dateTime('finished_date')->nullable();
            $table->boolean('is_complete')->default(0);
            $table->integer('sample_header_id')->default(0);
            $table->integer('tat_remark')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tat_captured');
    }
}


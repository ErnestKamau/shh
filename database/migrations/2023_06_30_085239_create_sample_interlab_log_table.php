<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSampleInterlabLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sample_interlab_log', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('sample_id');
            $table->integer('to_lab_section_id')->nullable();
            $table->integer('from_lab_section_id')->nullable();
            $table->string('quantity')->nullable();
            $table->integer('submited_by');
            $table->dateTime('date_submitted');
            $table->integer('received_by')->nullable();
            $table->dateTime('date_received')->nullable();
            $table->text('remarks')->nullable();
            $table->date('expected_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sample_interlab_log');
    }
}

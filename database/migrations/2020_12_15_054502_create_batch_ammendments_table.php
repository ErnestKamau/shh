<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchAmmendmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_ammendments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('batch_id');
            $table->integer('created_by_id');
            $table->text('reason');
            $table->string('samples');
            $table->string('report_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('batch_ammendments');
    }
}

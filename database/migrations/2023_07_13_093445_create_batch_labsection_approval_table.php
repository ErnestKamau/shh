<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchLabsectionApprovalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_labsection_approval', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('batch_id');
            $table->integer('user_id');
            $table->string('title');
            $table->string('lab_section_ids');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('batch_labsection_approval');
    }
}

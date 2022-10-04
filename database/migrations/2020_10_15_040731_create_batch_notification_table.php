<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_notifications', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('position_id');
            $table->integer('created_by');
            $table->text('notification');
            $table->integer('batch_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('batch_notification');
    }
}

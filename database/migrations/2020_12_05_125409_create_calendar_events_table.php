<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalendarEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('title');
            $table->string('description');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('responsible_id')->nullable();
            $table->integer('client_id')->nullable();
            $table->string('attachment')->nullable();
            $table->string('location')->nullable();
            $table->integer('created_by');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendar_events');
    }
}

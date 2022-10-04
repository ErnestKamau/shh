<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalendarEventsNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendarevents_notifications', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('duration');
            $table->string('rate');
            $table->integer('calendar_event_id');
            $table->boolean('active')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendar_events_notification');
    }
}

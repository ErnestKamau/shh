<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calendarevents_notifications', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('duration');
            $table->string('rate');
            $table->uuid('calendar_event_id')->index('idx_calendarevents_notifications_calendar_event_id_184c70de');
            $table->boolean('active')->default(true);
            $table->boolean('is_sent')->nullable()->default(false);
            $table->foreign(['calendar_event_id'], 'fk_calendarevents_notifications_calendar_event_id_fc31581d')->references(['id'])->on('calendar_events')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendarevents_notifications');
    }
};

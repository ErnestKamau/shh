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
        if (Schema::hasTable('calendarevents_notifications')) {
            return;
        }
        Schema::create('calendarevents_notifications', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('duration');
            $table->string('rate');
            $table->uuid('calendar_event_id')->index('idx_calendarevents_notifications_calendar_event_id_8d98af1d');
            $table->boolean('active')->default(true);
            $table->boolean('is_sent')->nullable()->default(false);

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

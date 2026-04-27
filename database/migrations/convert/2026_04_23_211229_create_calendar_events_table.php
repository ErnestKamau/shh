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
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->string('title');
            $table->text('description');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('responsible_id', 500)->default('73');
            $table->integer('client_id')->nullable();
            $table->string('attachment')->nullable();
            $table->string('location')->nullable();
            $table->uuid('created_by')->nullable()->index('idx_calendar_events_created_by');
            $table->string('status', 500)->nullable();
            $table->boolean('is_client_notify')->nullable()->default(true);
            $table->boolean('is_routine')->nullable()->default(false);
            $table->string('frequency', 200)->nullable();
            $table->longText('routine_events')->nullable();
            $table->boolean('has_notification')->nullable()->default(false);
            $table->time('start_time')->nullable()->default('00:00:00');
            $table->time('end_time')->nullable()->default('00:00:00');
            $table->boolean('notification_sent')->nullable()->default(false);
            $table->integer('parent_id')->nullable();
            $table->text('logistics')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->foreign(['created_by'], 'fk_calendar_events_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};

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
        Schema::create('personnel_working_schedules', function (Blueprint $table) {
            $table->time('start_timeslot');
            $table->time('end_timeslot');
            $table->uuid('id');
            $table->string('name', 100)->nullable();
            $table->string('title')->default('weekday');
            $table->string('day')->nullable();
            $table->string('slot_type')->default('include');
            $table->string('duration')->default('forever');
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_working_schedules');
    }
};

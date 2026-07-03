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
        if (Schema::hasTable('workorder_personnel_schedules')) {
            return;
        }
        Schema::create('workorder_personnel_schedules', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('personnel_id');
            $table->integer('workorder_id');
            $table->dateTime('start');
            $table->dateTime('end');
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workorder_personnel_schedules');
    }
};

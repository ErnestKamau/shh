<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentDailyLogEntriesTable extends Migration
{
    public function up()
    {
        Schema::create('equipment_daily_log_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('equipment_id');
            $table->integer('company_id');
            $table->date('log_date');
            $table->unsignedTinyInteger('slot_number');
            $table->string('recorded_value')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();

            $table->unique(['equipment_id', 'log_date', 'slot_number'], 'unique_equipment_log_slot');
            $table->index(['company_id', 'log_date']);
            $table->foreign('equipment_id')->references('id')->on('equipment')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('equipment_daily_log_entries');
    }
}

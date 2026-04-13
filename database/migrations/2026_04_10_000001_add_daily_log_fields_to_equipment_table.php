<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDailyLogFieldsToEquipmentTable extends Migration
{
    public function up()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('daily_log_value_type')->nullable()->after('requires_daily_log'); // constant | range
            $table->string('daily_log_nature')->nullable()->after('daily_log_value_type');   // qualitative | quantitative
            $table->unsignedTinyInteger('daily_log_tolerance')->nullable()->after('daily_log_nature'); // +/- n
        });
    }

    public function down()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn(['daily_log_value_type', 'daily_log_nature', 'daily_log_tolerance']);
        });
    }
}

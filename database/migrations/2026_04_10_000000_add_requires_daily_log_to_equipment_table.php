<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequiresDailyLogToEquipmentTable extends Migration
{
    public function up()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->boolean('requires_daily_log')->default(false)->after('active');
        });
    }

    public function down()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn('requires_daily_log');
        });
    }
}

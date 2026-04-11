<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDailyLogExpectedValuesToEquipmentTable extends Migration
{
    public function up()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('daily_log_expected_value')->nullable()->after('daily_log_tolerance');
            $table->decimal('daily_log_expected_min', 10, 4)->nullable()->after('daily_log_expected_value');
            $table->decimal('daily_log_expected_max', 10, 4)->nullable()->after('daily_log_expected_min');
        });
    }

    public function down()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn(['daily_log_expected_value', 'daily_log_expected_min', 'daily_log_expected_max']);
        });
    }
}

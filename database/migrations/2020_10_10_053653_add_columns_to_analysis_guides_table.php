<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToAnalysisGuidesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('analysis_guides', function (Blueprint $table) {
            $table->integer('standard_id')->nullable();
            $table->string('standard_value_type')->nullable();
            $table->string('high')->nullable();
            $table->string('low')->nullable();
            $table->integer('standard_value_id')->nullable();
            $table->string('standard_is_value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('analysis_guides', function (Blueprint $table) {
            //
        });
    }
}

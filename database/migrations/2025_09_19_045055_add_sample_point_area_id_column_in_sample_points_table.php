<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSamplePointAreaIdColumnInSamplePointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sample_points', function (Blueprint $table) {
            $table->bigInteger('sample_point_area_id')->unsigned()->nullable();
            $table->foreign('sample_point_area_id')->references('id')->on('sample_point_area')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sample_points', function (Blueprint $table) {
            //
        });
    }
}

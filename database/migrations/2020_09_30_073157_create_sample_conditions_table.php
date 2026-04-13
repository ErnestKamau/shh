<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSampleConditionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sample_conditions', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('name');
			$table->boolean('active');
			$table->integer('sample_type_id');
			$table->timestamps(6);
			$table->string('short_name')->nullable();
			$table->integer('reporting_time')->nullable()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sample_conditions');
	}

}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSampleDatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sample_dates', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('name');
			$table->dateTime('date');
			$table->timestamps(10);
			$table->bigInteger('sample_header_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sample_dates');
	}

}

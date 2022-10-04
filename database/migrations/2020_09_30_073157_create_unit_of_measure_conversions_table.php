<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnitOfMeasureConversionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('unit_of_measure_conversions', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->integer('uom1');
			$table->integer('uom2');
			$table->float('conversion', 10, 0);
			$table->timestamps(10);
			$table->integer('material_type_id')->nullable();
			$table->integer('location_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('unit_of_measure_conversions');
	}

}

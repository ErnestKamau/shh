<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrencyConversionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('currency_conversions', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('currency_1');
			$table->integer('currency_2');
			$table->float('ratio', 10, 0);
			$table->timestamps(6);
			$table->integer('inventory_location_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('currency_conversions');
	}

}

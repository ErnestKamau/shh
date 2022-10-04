<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePricelistCustomersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pricelist_customers', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('pricelist_id');
			$table->integer('customer_id');
			$table->timestamps(10);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('pricelist_customers');
	}

}

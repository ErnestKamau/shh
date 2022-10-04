<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryStoresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inventory_stores', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('name');
			$table->integer('company_id');
			$table->integer('inventory_location_id');
			$table->timestamps(10);
			$table->string('type_of_store', 100)->default('inventory_store');
			$table->boolean('is_frozen')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('inventory_stores');
	}

}

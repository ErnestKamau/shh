<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryOrderItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inventory_order_items', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('inventory_order_id');
			$table->integer('inventory_category_id');
			$table->integer('inventory_sub_category_id');
			$table->float('quantity', 10, 0);
			$table->boolean('fulfilled')->default(0);
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
		Schema::drop('inventory_order_items');
	}

}

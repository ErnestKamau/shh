<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryOrderItemToInventoryItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inventory_order_item_to_inventory_items', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('inventory_order_id');
			$table->integer('inventory_item_id');
			$table->integer('inventory_order_item_id');
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
		Schema::drop('inventory_order_item_to_inventory_items');
	}

}

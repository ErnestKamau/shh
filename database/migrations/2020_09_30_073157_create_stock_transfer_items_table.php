<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockTransferItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('stock_transfer_items', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->timestamps(10);
			$table->bigInteger('stock_transfer_id');
			$table->bigInteger('local_item_id');
			$table->bigInteger('local_store_id');
			$table->bigInteger('local_store_slot_id');
			$table->bigInteger('target_item_id');
			$table->bigInteger('target_store_id');
			$table->bigInteger('target_store_slot_id');
			$table->float('target_quantity', 10, 0);
			$table->integer('local_inventory_item_id')->nullable();
			$table->integer('target_inventory_item_id')->nullable();
			$table->date('expiry')->nullable();
			$table->integer('local_state_id')->nullable();
			$table->integer('target_state_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('stock_transfer_items');
	}

}

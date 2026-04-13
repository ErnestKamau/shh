<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockTakingSheetsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('stock_taking_sheets', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('stock_taking_id');
			$table->integer('inventory_sub_category_id');
			$table->string('code');
			$table->string('store_name');
			$table->integer('store_id');
			$table->string('slot_name');
			$table->integer('slot_id');
			$table->float('system_quantity', 10, 0);
			$table->float('available_quantity', 10, 0)->nullable();
			$table->timestamps(6);
			$table->text('comments')->nullable();
			$table->integer('adjusted_inventory_item_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('stock_taking_sheets');
	}

}

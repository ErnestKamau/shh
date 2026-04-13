<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryStoreSlotContentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inventory_store_slot_contents', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('inventory_store_slot_id');
			$table->integer('inventory_item_id');
			$table->timestamps(6);
			$table->integer('inventory_sub_category_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('inventory_store_slot_contents');
	}

}

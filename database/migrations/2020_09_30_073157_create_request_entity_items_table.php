<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestEntityItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('request_entity_items', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('request_id');
			$table->integer('store_id');
			$table->integer('slot_id');
			$table->integer('inventory_sub_category_id');
			$table->decimal('quantity', 10, 0);
			$table->decimal('net_value', 10, 0);
			$table->timestamps(10);
			$table->string('action', 100)->default('normal');
			$table->string('status', 100)->default('pending');
			$table->date('gr_expiry')->default('2099-12-31');
			$table->integer('inventory_item_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('request_entity_items');
	}

}

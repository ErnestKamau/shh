<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryOrdersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inventory_orders', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('order_number');
			$table->string('supplier_id')->nullable();
			$table->integer('created_by');
			$table->string('status')->default('not_fulfilled');
			$table->timestamps(10);
			$table->integer('company_id')->default(1);
			$table->string('comments', 512)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('inventory_orders');
	}

}
